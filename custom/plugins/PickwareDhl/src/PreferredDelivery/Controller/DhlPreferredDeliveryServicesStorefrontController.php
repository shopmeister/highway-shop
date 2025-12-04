<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\PickwareDhl\PreferredDelivery\Controller;

use DateInterval;
use DateTime;
use Exception;
use Pickware\DalBundle\EntityManager;
use Pickware\HttpUtils\ResponseFactory;
use Pickware\PickwareDhl\Config\DhlConfig;
use Pickware\PickwareDhl\PreferredDelivery\ParcelManagementApiClientFactory;
use Pickware\PickwareDhl\PreferredDelivery\Requests\GetAvailableServicesRequest;
use Pickware\ShippingBundle\Config\ConfigService;
use Pickware\ShippingBundle\Config\Model\ShippingMethodConfigDefinition;
use Pickware\ShippingBundle\Config\Model\ShippingMethodConfigEntity;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\GenericPageLoader;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(defaults: ['_routeScope' => ['storefront']])]
class DhlPreferredDeliveryServicesStorefrontController extends StorefrontController
{
    private const PHP_SUNDAY_IDENTIFIER = '0';

    public function __construct(
        private readonly GenericPageLoader $genericPageLoader,
        private readonly EntityManager $entityManager,
        private readonly ConfigService $configService,
        #[Autowire(service: 'monolog.logger.business_events')]
        private readonly LoggerInterface $logger,
        private readonly ParcelManagementApiClientFactory $parcelManagementApiClient,
    ) {}

    #[Route(
        path: '/pickware-dhl/preferred-delivery',
        name: 'pickware-dhl.frontend.preferred-delivery.page',
        options: ['seo' => false],
        defaults: ['XmlHttpRequest' => true],
        methods: ['POST'],
    )]
    public function editPreferredDelivery(Request $request, SalesChannelContext $context): Response
    {
        $zipCode = $request->get('zipCode');
        if (!$zipCode) {
            return ResponseFactory::createParameterMissingResponse('zipCode');
        }

        /** @var ShippingMethodConfigEntity $shippingMethodConfiguration */
        $shippingMethodConfiguration = $this->entityManager->findOneBy(
            ShippingMethodConfigDefinition::class,
            (new Criteria())->addFilter(new EqualsFilter('shippingMethodId', $context->getShippingMethod()->getId())),
            $context->getContext(),
        );
        $shippingMethodConfig = $shippingMethodConfiguration ? $shippingMethodConfiguration->getStorefrontConfig() : [];

        $availableServices = $this->getAvailableServices($zipCode, $context->getSalesChannelId(), $shippingMethodConfig);

        $page = $this->genericPageLoader->load($request, $context);

        $response = $this->renderStorefront(
            '@Storefront/storefront/pickware-dhl/preferred-delivery-form/preferred-delivery-form.html.twig',
            array_merge(
                [
                    'page' => $page,
                    'availableServices' => $availableServices,
                ],
                $shippingMethodConfig,
            ),
        );

        // Do not index this site, as it should only be reached when at checkout
        $response->headers->set('x-robots-tag', 'noindex');

        return $response;
    }

    private function getAvailableServices(string $zipCode, string $salesChannelId, array $shippingMethodConfig): array
    {
        $dhlConfig = new DhlConfig(
            $this->configService->getConfigForSalesChannel(DhlConfig::CONFIG_DOMAIN, $salesChannelId),
        );

        $startDate = new DateTime();
        $daysToAddToDate = 0;
        // The field 'lastOrderTime' holds a time string with a timezone. A DateTime instantiated with only a time
        // string is equivalent to a DateTime instantiated with the current date with the time string. Thus we can use
        // the timestamp comparison here without worrying about the current date.
        if (
            array_key_exists('lastOrderTime', $dhlConfig->getConfig()->getRawConfig())
            && $startDate->getTimestamp() > (new DateTime($dhlConfig->getConfig()['lastOrderTime']))->getTimestamp()
        ) {
            $daysToAddToDate = $daysToAddToDate + 1;
        }
        if (array_key_exists('processingTimeInDays', $shippingMethodConfig)) {
            $daysToAddToDate = $daysToAddToDate + $shippingMethodConfig['processingTimeInDays'];
        }
        $startDate->add(new DateInterval(sprintf(
            'P%sD',
            $daysToAddToDate,
        )));

        $parcelManagementApiClient = $this->parcelManagementApiClient->createDhlParcelManagementApiClient(
            $dhlConfig->getDhlApiClientConfig(),
            $dhlConfig->getCustomerNumber(),
        );

        try {
            $availableServicesResponse = $parcelManagementApiClient->sendRequest(new GetAvailableServicesRequest($zipCode, $startDate));
        } catch (Exception $exception) {
            $this->logger->error('Could not retrieve dhl preferred delivery services', [
                'message' => $exception->getMessage(),
                'stackTrace' => $exception->getTraceAsString(),
                'code' => $exception->getCode(),
                'zipCode' => $zipCode,
            ]);
        }

        if (isset($availableServicesResponse) && $availableServicesResponse->getStatusCode() === Response::HTTP_OK) {
            $availableServices = json_decode(
                (string) $availableServicesResponse->getBody(),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } else {
            $availableServices = [];
        }

        $excludedDays = $dhlConfig->getConfig()['excludedDeliveryDays'] ?? [];
        if (array_key_exists('preferredDay', $availableServices) && $availableServices['preferredDay']['available']) {
            $validDays = $availableServices['preferredDay']['validDays'];

            // Filter out all valid days if the weekday they specify is excluded
            $availableDays = array_values(array_filter($validDays, function(array $dayTimeFrame) use ($excludedDays) {
                $currentWeekdayNumber = (new DateTime($dayTimeFrame['start']))->format('w');

                return !in_array($currentWeekdayNumber, $excludedDays) && $currentWeekdayNumber !== self::PHP_SUNDAY_IDENTIFIER;
            }));

            $availableServices['preferredDay']['validDays'] = $availableDays;
        }

        return $availableServices;
    }
}
