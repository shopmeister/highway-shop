<?php declare(strict_types=1);

namespace NetzpShopmanager6\Subscriber;

use NetzpShopmanager6\Components\PushMessageHelper;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FrontendSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly SystemConfigService $config,
                                private readonly PushMessageHelper $pushHelper,
                                private readonly EntityRepository $languageRepository,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutOrderPlacedEvent::class => 'onOrder'
        ];
    }

    public function onOrder(CheckoutOrderPlacedEvent $event): void
    {
        $config = $this->config->get('NetzpShopmanager6.config');
        if(array_key_exists('usepush', $config) && $config['usepush'])
        {
            $salesChannelId = $event->getSalesChannelId();

            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('id', $event->getContext()->getLanguageId()));
            $criteria->addAssociation('locale');

            $language = $this->languageRepository->search($criteria, $event->getContext())->getEntities()->first();
            $localeCode = 'de';

            if($language != null) {
                $localeCode = $language->getLocale()->getCode() ?? 'de';
            }
            $localeCode = strtolower(substr((string) $localeCode, 0, 2));

            $template = $this->config->get('NetzpShopmanager6.config.pushtemplate', $salesChannelId);
            if ($template == '') {
                $template = PushMessageHelper::DEFAULT_PUSH_TEMPLATE;
            }

            $this->pushHelper->sendPushMessage(
                $event->getOrder(),
                $event->getContext(),
                $template,
                $salesChannelId,
                null,
                $localeCode);
        }
    }
}
