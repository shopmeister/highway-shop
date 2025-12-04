<?php
/*
 * Copyright (c) Pickware GmbH. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Pickware\ApiErrorHandlingBundle\ControllerExceptionHandling;

use Pickware\ApiErrorHandlingBundle\ServerOverloadException\ServerOverloadException;
use Pickware\HttpUtils\JsonApi\JsonApiErrors;
use Pickware\HttpUtils\JsonApi\JsonApiErrorSerializable;
use Pickware\HttpUtils\JsonApi\JsonApiErrorsSerializable;
use function Pickware\PhpStandardLibrary\Language\convertExceptionToArray;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\PlatformRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Throwable;

class AdminApiJsonApiErrorSerializableExceptionHandler implements EventSubscriberInterface
{
    // Use Priority 0 because Shopware uses -1 in its  ResponseExceptionListener, and we want to run BEFORE
    // Shopware. Otherwise, Shopware would handle our error.
    public const PRIORITY = 0;

    private bool $debug;
    private LoggerInterface $logger;

    public function __construct(bool $debug, LoggerInterface $logger)
    {
        $this->debug = $debug;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => [
                'onKernelException',
                self::PRIORITY,
            ],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if (!($throwable instanceof JsonApiErrorSerializable) && !($throwable instanceof JsonApiErrorsSerializable)) {
            return;
        }

        /** @var Context $context */
        $context = $event->getRequest()->attributes->get(PlatformRequest::ATTRIBUTE_CONTEXT_OBJECT);

        // Only the Admin API uses JSON API therefore we can only respond with JSON API error here.
        if (!$context || !($context->getSource() instanceof AdminApiSource)) {
            return;
        }

        if ($throwable instanceof JsonApiErrorSerializable) {
            $errors = new JsonApiErrors([$throwable->serializeToJsonApiError()]);
        } else {
            $errors = $throwable->serializeToJsonApiErrors();
        }
        $exceptionDetails = $this->getExceptionDetails($throwable);
        $this->logger->error(
            sprintf('Caught JsonApiErrorSerializable exception in admin controller: %s', $throwable->getMessage()),
            [
                'exception' => $exceptionDetails,
                'jsonApiErrors' => $errors,
                'request' => [
                    // No headers are logged because they may contain sensitive information like API keys
                    'url' => $event->getRequest()->getUri(),
                    'method' => $event->getRequest()->getMethod(),
                    'body' => $event->getRequest()->getContent(),
                ],
            ],
        );

        if ($throwable instanceof ServerOverloadException) {
            $httpStatusCode = Response::HTTP_SERVICE_UNAVAILABLE;
        } else {
            $httpStatusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $response = $errors->toJsonApiErrorResponse($httpStatusCode);

        if ($this->debug) {
            $json = json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR);
            $json->_exceptionDetails = $exceptionDetails;
            $response->setData($json);
        }

        $event->setResponse($response);
    }

    private function getExceptionDetails(Throwable $exception): array
    {
        $details = convertExceptionToArray($exception);
        $previous = $exception->getPrevious();

        if ($previous) {
            $details['previous'] = self::getExceptionDetails($previous);

            if ($previous instanceof JsonApiErrorSerializable) {
                $details['previous']['jsonApiError'] = $previous->serializeToJsonApiError();
            }
            if ($previous instanceof JsonApiErrorsSerializable) {
                $details['previous']['jsonApiErrors'] = $previous->serializeToJsonApiErrors();
            }
        }

        return $details;
    }
}
