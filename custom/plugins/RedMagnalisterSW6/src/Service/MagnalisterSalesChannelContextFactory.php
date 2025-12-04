<?php declare(strict_types=1);

namespace Redgecko\Magnalister\Service;

use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Session\SessionFactory;


class MagnalisterSalesChannelContextFactory extends AbstractSalesChannelContextFactory
{
    private AbstractSalesChannelContextFactory $innerService;
    protected SessionFactory $session;

    public function __construct(
        AbstractSalesChannelContextFactory $innerService,
        SessionFactory $session

    ) {
        $this->innerService = $innerService;
        $this->session = $session;

    }

    public function getDecorated(): AbstractSalesChannelContextFactory
    {
        return $this->innerService;
    }

    public function create(string $token, string $salesChannelId, array $options = []): SalesChannelContext
    {
        return $this->innerService->create($token, $salesChannelId, $options);
    }


}
