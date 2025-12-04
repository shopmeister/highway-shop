<?php declare(strict_types=1);

namespace Swag\AmazonPay\Administration\Controller;

use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Swag\AmazonPay\Components\PaymentNotification\Exception\InvalidPaymentNotificationException;
use Swag\AmazonPay\Components\PaymentNotification\Exception\PaymentNotificationProcessException;
use Swag\AmazonPay\Components\PaymentNotification\PaymentNotificationHandlerRegistryInterface;
use Swag\AmazonPay\Components\PaymentNotification\Validation\PaymentNotificationValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class AmazonPayInstanPaymentNotificationController extends AbstractController
{
    private PaymentNotificationHandlerRegistryInterface $paymentNotificationHandlerRegistry;

    private LoggerInterface $logger;

    private PaymentNotificationValidatorInterface $notificationValidator;

    public function __construct(
        PaymentNotificationHandlerRegistryInterface $paymentNotificationHandlerRegistry,
        LoggerInterface                             $logger,
        PaymentNotificationValidatorInterface       $notificationValidator
    )
    {
        $this->paymentNotificationHandlerRegistry = $paymentNotificationHandlerRegistry;
        $this->logger = $logger;
        $this->notificationValidator = $notificationValidator;
    }

    #[Route(path: 'api/_action/swag_amazon_pay/ipn', name: 'api.action.swag_amazon_pay.ipn', defaults: ['auth_required' => false], methods: ['POST'])]
    public function paymentNotification(Request $request, Context $context): Response
    {
        $this->logger->debug('🌐ℹ️ Payment notification received', [$request->getContent()]);

        try {
            $notificationMessage = $this->notificationValidator->validate($request, $context);
            $notificationHandler = $this->paymentNotificationHandlerRegistry->getHandler($notificationMessage->getObjectType());
            $notificationHandler->process($notificationMessage, $context);

            $this->logger->debug('🌐✔️ Payment notification processed successfully', ['notificationId' => $notificationMessage->getNotificationId()]);
        } catch (InvalidPaymentNotificationException $e) {
            $this->logger->warning(\sprintf('Could not handle payment notification: %s', $e->getMessage()), ['body' => $e->getNotificationBody()]);
        }catch (PaymentNotificationProcessException $e) {
            $this->logger->warning(\sprintf('Could not handle payment notification: %s', $e->getMessage()), ['body' => $request->getContent()]);
        } catch (Exception $e) {
            $this->logger->warning(\sprintf('Could not handle payment notification: %s', $e->getMessage()));
            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response();
    }
}
