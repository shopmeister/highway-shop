<?php

namespace ShopmasterZalandoConnectorSix\Services\Import\Product\PriceReport;

use ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product\ApiZalandoProductPriceAttemptService;
use ShopmasterZalandoConnectorSix\Struct\Product\Psr\PsrProductCollection;

class ImportPriceReportService
{

    private ApiZalandoProductPriceAttemptService $apiZalandoProductPriceAttemptService;

    public function __construct(
        ApiZalandoProductPriceAttemptService $apiZalandoProductPriceAttemptService
    )
    {
        $this->apiZalandoProductPriceAttemptService = $apiZalandoProductPriceAttemptService;
    }

    public function runImportProcessByPsr(PsrProductCollection $psr)
    {
        $this->apiZalandoProductPriceAttemptService->makePriceReportByPsr($psr);

    }

}