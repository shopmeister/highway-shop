<?php declare(strict_types=1);

namespace ShopmasterZalandoConnectorSix\Services\Api\Zalando\Product;

use ShopmasterZalandoConnectorSix\Services\HttpClient\ClientService;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\RequestStruct;
use ShopmasterZalandoConnectorSix\Struct\HttpClient\ResponseStruct;

class ApiZalandoProductService
{
    private ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Check if a product exists at Zalando by EAN
     *
     * @param string $ean
     * @param string $merchantId
     * @return ResponseStruct
     */
    public function checkProductExistsByEan(string $ean, string $merchantId): ResponseStruct
    {
        $request = new RequestStruct();
        $request->setMethodName($request::METHOD_GET)
            ->setUrl('/products/identifiers/' . $ean)
            ->setUseMerchantId(false);

        return $this->clientService->request($request);
    }

    /**
     * Onboard existing product to Zalando
     *
     * @param array $payload
     * @param string $merchantId
     * @return ResponseStruct
     */
    public function onboardExistingProduct(array $payload, string $merchantId, string $ean): ResponseStruct
    {
        $request = new RequestStruct();
        $request->setMethodName($request::METHOD_PUT)
            ->setUrl('/products/identifiers/' . $ean)
            ->setContent($payload)
            ->setUseMerchantId(true);

        return $this->clientService->request($request);
    }


    /**
     * Get products by EAN list (for bulk checking) - currently not supported by REST API
     * Use individual checks for each EAN instead
     *
     * @param array $eans
     * @param string $merchantId
     * @return array
     */
    public function getProductsByEans(array $eans, string $merchantId): array
    {
        $results = [];
        
        foreach ($eans as $ean) {
            $response = $this->checkProductExistsByEan($ean, $merchantId);
            $results[$ean] = [
                'exists' => $response->isSuccessStatus() && 
                           !empty($response->getContentArray()['items'] ?? []),
                'response' => $response
            ];
            
            // Rate limiting - avoid overwhelming the API
            usleep(100000); // 100ms delay between requests
        }
        
        return $results;
    }
}