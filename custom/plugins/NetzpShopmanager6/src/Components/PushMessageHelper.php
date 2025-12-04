<?php declare(strict_types=1);

namespace NetzpShopmanager6\Components;

use Shopware\Core\Content\Flow\Dispatching\StorableFlow;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class PushMessageHelper
{
    final public const DEFAULT_PUSH_TEMPLATE = '[{shop}] Neue Bestellung im Shop von {name} ({email}). Positionen: {positions}, Zahlart: {payment}, Summe: {total} Datum: {ordertime} (UTC)';
    final public const PROXY_URL                    = 'https://sm.netzperfekt.de/api/'; // with trailing /

    final public const NOTIFICATION_TYPE_MSG   = 1;
    final public const NOTIFICATION_TYPE_ORDER = 2;

    public function __construct(private readonly EntityRepository $integrationRepository,
                                private readonly EntityRepository $languageRepository,
                                private readonly ShopmanagerHelper $helper)
    {
    }

    public function sendPushMessage(?Entity $entity,
                                    Context $context,
                                    $template,
                                    $salesChannelId,
                                    StorableFlow $flow = null,
                                    $localeCode = 'de'): void
    {
        // send push notification to device via netzperfekt & google firebase cloud messaging (fcm)
        // no data will be permanently stored, evaluated or forwarded to another location!

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFields.netzp_shopmanager_type', 'sm'));
        $integration = $this->integrationRepository->search($criteria, new Context(new SystemSource()))->getEntities()->first();

        if( ! $integration) {
            return;
        }

        $apiKey = 'SW6_' .
                    $integration->getAccessKey() . '_' .
                    $integration->getCustomFields()['netzp_shopmanager_key'];

        $msg = $this->getPushTemplate($entity, $context, $template, $salesChannelId, $flow);
        $msg = str_replace('/', '|', $msg); // urlencoded / (%2F) is still breaking the url path...

        $url = self::PROXY_URL . 'push/' .
            $apiKey . '/' .
            $salesChannelId . '/' .
            self::NOTIFICATION_TYPE_ORDER . '/' .
            urlencode($msg) . '/' .
            $localeCode;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_URL             => $url
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $curl = null;
    }

    private function getPushTemplate(?Entity $entity,
                                     Context $context,
                                     string $template,
                                     $salesChannelId,
                                     ?StorableFlow $flow = null): string
    {
        if($entity instanceof OrderEntity)
        {
            return $this->replaceVariablesForOrder(
                $entity,
                $context,
                $salesChannelId,
                $template
            );
        }
        elseif($flow->hasStore('contactFormData'))
        {
            return $this->replaceVariablesForContactForm(
                $flow->getStore('contactFormData'),
                $context,
                $salesChannelId,
                $template
            );
        }

        return '';
    }

    public function handlePushMessageForFlowBuilderEvent(StorableFlow $flow,
                                                         Context $context,
                                                         string $template): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $flow->getContext()->getLanguageId()));
        $criteria->addAssociation('locale');

        $language = $this->languageRepository->search($criteria, $flow->getContext())->getEntities()->first();
        $localeCode = 'de';
        if($language != null) {
            $localeCode = $language->getLocale()->getCode() ?? 'de';
        }
        $localeCode = strtolower(substr((string) $localeCode, 0, 2));

        if($flow->hasStore('orderId'))
        {
            $order = $flow->getData('order');
            $this->sendPushMessage(
                $order,
                $context,
                $template,
                $order->getSalesChannelId(),
                $flow,
                $localeCode);
        }
        elseif($flow->hasStore('contactFormData'))
        {
            $this->sendPushMessage(
                null,
                $context,
                $template,
                $flow->getStore('salesChannelId'),
                $flow,
                $localeCode);
        }
    }

    private function replaceVariablesForOrder(OrderEntity $order,
                                              Context $context,
                                              $salesChannelId,
                                              string $template): string
    {
        $shopName = $this->getShopName($context, $salesChannelId);
        $name = $order->getOrderCustomer()->getLastName() . ', ' . $order->getOrderCustomer()->getFirstName();
        $email = $order->getOrderCustomer()->getEmail();
        $paymentName = $order->getTransactions()->last()->getPaymentMethod()->getName() ?? '-';

        $s = $template;
        $s = str_replace('{shop}', $shopName, $s);
        $s = str_replace('{ordernumber}', $order->getOrderNumber(), $s);
        $s = str_replace('{ordertime}', $order->getOrderDateTime()->format('d.m.y H:i'), $s);
        $s = str_replace('{total}', $order->getAmountTotal() . ' ' . $order->getCurrency()->getSymbol(), $s);
        $s = str_replace('{positions}', (string)$order->getLineItems()->count(), $s);
        $s = str_replace('{payment}', $paymentName, $s);
        $s = str_replace('{name}', $name, $s);
        $s = str_replace('{email}', $email, $s);
        $s = str_replace('{comment}', $order->getCustomerComment() ?? '', $s);

        return $s;
    }

    private function replaceVariablesForContactForm(array $contactFormData,
                                                    Context $context,
                                                    $salesChannelId,
                                                    string $template): string
    {
        $shopName = $this->getShopName($context, $salesChannelId);

        $s = $template;
        $s = str_replace('{shop}', $shopName, $s);
        $s = str_replace('{date}', (new \DateTime())->format('d.m.y H:i'), $s);
        $s = str_replace('{salutation}', $contactFormData['salutation']->getDisplayName() ?? '', $s);
        $s = str_replace('{name}', $contactFormData['lastName'] . ', ' . $contactFormData['firstName'], $s);
        $s = str_replace('{email}', $contactFormData['email'], $s);
        $s = str_replace('{phone}', $contactFormData['phone'], $s);
        $s = str_replace('{subject}', $contactFormData['subject'], $s);
        $s = str_replace('{comment}', $contactFormData['comment'], $s);

        return $s;
    }

    private function getShopname(Context $context, $salesChannelId): string
    {
        $shopName = '';
        $salesChannels = $this->helper->getSalesChannels($context, $salesChannelId);
        if((is_countable($salesChannels) ? count($salesChannels) : 0) > 0) {
            $shopName = $salesChannels[0]['shopname'] ?? '';
        }

        return $shopName;
    }
}
