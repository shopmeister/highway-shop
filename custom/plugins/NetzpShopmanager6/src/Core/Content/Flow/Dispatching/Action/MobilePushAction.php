<?php declare(strict_types=1);

namespace NetzpShopmanager6\Core\Content\Flow\Dispatching\Action;

use NetzpShopmanager6\Components\PushMessageHelper;
use NetzpShopmanager6\Core\Framework\Event\MobilePushAware;
use Shopware\Core\Content\Flow\Dispatching\Action\FlowAction;
use Shopware\Core\Content\Flow\Dispatching\StorableFlow;

class MobilePushAction extends FlowAction
{
    public function __construct(private readonly PushMessageHelper $pushHelper)
    {
    }

    public static function getName(): string
    {
        return 'action.netzp.mobilepush';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            self::getName() => 'handle',
        ];
    }

    public function requirements(): array
    {
        return [MobilePushAware::class];
    }

    public function handleFlow(StorableFlow $flow): void
    {
        $config = $flow->getConfig();
        if ( ! \array_key_exists('template', $config)) {
            return;
        }

        $template = $config['template'];
        if (empty($template)) {
            return;
        }

        try {
            $this->pushHelper->handlePushMessageForFlowBuilderEvent(
                $flow,
                $flow->getContext(),
                $template);
        }
        catch (\Exception) {
            //
        }
    }
}
