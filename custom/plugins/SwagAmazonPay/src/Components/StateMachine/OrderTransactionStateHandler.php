<?php declare(strict_types=1);

namespace Swag\AmazonPay\Components\StateMachine;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler as CoreOrderTransactionStateHandler;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionEntity;
use Shopware\Core\System\StateMachine\Exception\IllegalTransitionException;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;
use Swag\AmazonPay\Components\Config\ConfigServiceInterface;
use Swag\AmazonPay\Components\Config\Validation\Exception\ConfigValidationException;
use Swag\AmazonPay\Components\StateMachine\Exception\NoTransitionException;

readonly class OrderTransactionStateHandler implements OrderTransactionStateHandlerInterface
{
    public function __construct(
        private StateMachineRegistry             $stateMachineRegistry,
        private EntityRepository                 $transitionRepository,
        private EntityRepository                 $orderTransactionRepository,
        private CoreOrderTransactionStateHandler $coreOrderTransactionStateHandler,
        private ConfigServiceInterface           $configService,
        private LoggerInterface                  $logger
    )
    {
    }

    public function authorize(OrderTransactionEntity $transaction, Context $context): void
    {
        try {
            $transition = $this->getTransition(self::STATE_TRANSACTION_AUTHORIZE, $transaction, $context);
        } catch (IllegalTransitionException $e) {
            $this->logger->notice('Invalid transition configured on authorize', ['message' => $e->getMessage()]);
            return;
        } catch (NoTransitionException) {
            return;
        }

        if ($transition === null) {
            return;
        }
        try {
            $this->transition($transaction, $transition->getActionName(), $context);
        } catch (\Exception $e) {
            $this->logger->notice('Invalid transition tried on authorize', ['message' => $e->getMessage()]);
        }
    }

    public function pay(OrderTransactionEntity $transaction, Context $context): void
    {
        try {
            $transition = $this->getTransition(self::STATE_TRANSACTION_CHARGE, $transaction, $context);
        } catch (IllegalTransitionException $e) {
            $this->logger->notice('Invalid transition configured on charge', ['message' => $e->getMessage()]);

            return;
        } catch (NoTransitionException) {
            return;
        }
        try {
            if ($transition === null) {
                $this->coreOrderTransactionStateHandler->paid($transaction->getId(), $context);

                return;
            }

            $this->transition($transaction, $transition->getActionName(), $context);
        } catch (\Exception $e) {
            $this->logger->notice('Invalid transition tried on capture', ['message' => $e->getMessage()]);
            if ($transition->getActionName() === 'pay') {
                $this->logger->info('Trying transition "paid" instead of "pay"', ['transactionId' => $transaction->getId()]);
                try {
                    $this->transition($transaction, 'paid', $context);
                } catch (\Exception $eFinal) {
                    $this->logger->notice('Invalid transition tried on capture', ['message' => $eFinal->getMessage()]);
                }
            }
        }
    }

    public function payPartially(OrderTransactionEntity $transaction, Context $context): void
    {
        try {
            $transition = $this->getTransition(self::STATE_TRANSACTION_PARTIAL_CHARGE, $transaction, $context);
        } catch (IllegalTransitionException $e) {
            $this->logger->notice('Invalid transition configured on partial charge', ['message' => $e->getMessage()]);

            return;
        } catch (NoTransitionException) {
            return;
        }
        try {
            if ($transition === null) {
                $this->coreOrderTransactionStateHandler->payPartially($transaction->getId(), $context);

                return;
            }

            $this->transition($transaction, $transition->getActionName(), $context);
        } catch (\Exception $e) {
            $this->logger->notice('Invalid transition tried on partial capture', ['message' => $e->getMessage()]);
        }
    }

    public function refund(OrderTransactionEntity $transaction, Context $context): void
    {
        try {
            $transition = $this->getTransition(self::STATE_TRANSACTION_REFUND, $transaction, $context);
        } catch (IllegalTransitionException $e) {
            $this->logger->notice('Invalid transition configured on refund', ['message' => $e->getMessage()]);

            return;
        } catch (NoTransitionException) {
            return;
        }
        try {
            if ($transition === null) {
                $this->coreOrderTransactionStateHandler->refund($transaction->getId(), $context);

                return;
            }

            $this->transition($transaction, $transition->getActionName(), $context);
        } catch (\Exception $e) {
            $this->logger->notice('Invalid transition tried on refund', ['message' => $e->getMessage()]);
        }
    }

    public function refundPartially(OrderTransactionEntity $transaction, Context $context): void
    {
        try {
            $transition = $this->getTransition(self::STATE_TRANSACTION_PARTIAL_REFUND, $transaction, $context);
        } catch (IllegalTransitionException $e) {
            $this->logger->notice('Invalid transition configured on partial refund', ['message' => $e->getMessage()]);
            return;
        } catch (NoTransitionException) {
            return;
        }
        try {
            if ($transition === null) {
                $this->coreOrderTransactionStateHandler->refundPartially($transaction->getId(), $context);
                return;
            }

            $this->transition($transaction, $transition->getActionName(), $context);
        } catch (\Exception $e) {
            $this->logger->notice('Invalid transition tried on partial refund', ['message' => $e->getMessage()]);
        }
    }

    public function cancel(OrderTransactionEntity $transaction, Context $context): void
    {
        try {
            $transition = $this->getTransition(self::STATE_TRANSACTION_CANCEL, $transaction, $context);
        } catch (IllegalTransitionException $e) {
            $this->logger->notice('Invalid transition configured on cancel', ['message' => $e->getMessage()]);

            return;
        } catch (NoTransitionException) {
            return;
        }
        try {
            if ($transition === null) {
                $this->coreOrderTransactionStateHandler->cancel($transaction->getId(), $context);

                return;
            }

            $this->transition($transaction, $transition->getActionName(), $context);
        } catch (\Exception $e) {
            $this->logger->notice('Invalid transition tried on cancel', ['message' => $e->getMessage()]);
        }
    }

    private function transition(OrderTransactionEntity $orderTransaction, string $transitionName, Context $context): void
    {
        try {
            $this->stateMachineRegistry->transition(
                new Transition(
                    OrderTransactionDefinition::ENTITY_NAME,
                    $orderTransaction->getId(),
                    $transitionName,
                    'stateId'
                ),
                $context
            );
        } catch (\Throwable $e) {
            $this->logger->notice('Invalid transition', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @throws IllegalTransitionException|InconsistentCriteriaIdsException|NoTransitionException
     */
    private function getTransition(string $transitionType, OrderTransactionEntity $transaction, Context $context): ?StateMachineTransitionEntity
    {
        // get current version to avoid race conditions with IPN
        $transaction = $this->orderTransactionRepository->search(
            (new Criteria([$transaction->getId()]))
                ->addAssociation('order'),
            $context
        )->first();
        $order = $transaction->getOrder();

        if ($order === null) {
            return null;
        }

        try {
            $pluginConfig = $this->configService->getPluginConfig($order->getSalesChannelId());
        } catch (ConfigValidationException $e) {
            return null;
        }

        $configGetter = self::CONFIG_PAYMENT_STATE_MAPPING_GETTER_PREFIX . $transitionType;
        $targetStateId = $pluginConfig->$configGetter();

        if ($targetStateId === null || Uuid::isValid($targetStateId) === false) {
            return null;
        }

        if ($targetStateId === $transaction->getStateId()) {
            $this->logger->debug('No status transition necessary for ' . $transitionType, ['orderId' => $order->getId(), 'orderNumber' => $order->getOrderNumber(), 'state' => $targetStateId]);
            throw new NoTransitionException();
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('fromStateId', $transaction->getStateId()));
        $criteria->addFilter(new EqualsFilter('toStateId', $targetStateId));

        $transition = $this->transitionRepository->search($criteria, $context)->first();

        if ($transition === null) {
            ///** @var StateMachineStateEntity $state */
            //$state = $transaction->getStateMachineState();

            throw new IllegalTransitionException($transaction->getStateId(), $transitionType, []);
        }

        return $transition;
    }
}
