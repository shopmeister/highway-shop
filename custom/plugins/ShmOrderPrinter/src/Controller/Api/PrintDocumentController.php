<?php declare(strict_types=1);

namespace Shm\OrderPrinter\Controller\Api;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class PrintDocumentController extends AbstractController
{
    private Connection $connection;
    private EntityRepositoryInterface $orderRepository;
    private EntityRepositoryInterface $documentRepository;

    public function __construct(
        Connection $connection,
        EntityRepositoryInterface $orderRepository,
        EntityRepositoryInterface $documentRepository
    ) {
        $this->connection = $connection;
        $this->orderRepository = $orderRepository;
        $this->documentRepository = $documentRepository;
    }

    #[Route(path: '/api/shm-print-documents', name: 'api.shm.print.documents', methods: ['POST'])]
    public function printDocuments(Request $request, Context $context): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['orders']) || !is_array($data['orders'])) {
            return new JsonResponse(['error' => 'Missing or invalid orders data'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $orderIds = array_keys($data['orders']);

            // Fetch orders with associations
            $criteria = new Criteria($orderIds);
            $criteria->addAssociation('lineItems.product');
            $criteria->addAssociation('deliveries.shippingOrderAddress');
            $criteria->addAssociation('salesChannel');
            $criteria->addAssociation('currency');

            $orders = $this->orderRepository->search($criteria, $context);

            $results = [];
            foreach ($orders->getElements() as $order) {
                $results[] = [
                    'orderId' => $order->getId(),
                    'orderNumber' => $order->getOrderNumber(),
                    'status' => 'processed'
                ];
            }

            return new JsonResponse([
                'success' => true,
                'processed' => count($results),
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to process documents: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/api/shm-seven-senders/get-label-url', name: 'api.shm.seven.senders.labels', methods: ['GET'])]
    public function getSevenSendersLabels(Request $request, Context $context): JsonResponse
    {
        $orderNumber = $request->query->get('orderNumber');

        if (!$orderNumber) {
            return new JsonResponse(['error' => 'Missing orderNumber parameter'], Response::HTTP_BAD_REQUEST);
        }

        // This is a placeholder for SevenSenders integration
        // In a real implementation, you would integrate with SevenSenders API
        return new JsonResponse([
            'data' => [
                [
                    'url' => 'https://example.com/shipping-label/' . $orderNumber,
                    'blob' => base64_encode('placeholder-pdf-content-shipping'),
                    'type' => 'outbound'
                ],
                [
                    'url' => 'https://example.com/return-label/' . $orderNumber,
                    'blob' => base64_encode('placeholder-pdf-content-return'),
                    'type' => 'return'
                ]
            ]
        ]);
    }

    #[Route(path: '/api/pickware-document/{documentId}/contents', name: 'api.shm.pickware.document', methods: ['GET'])]
    public function getPickwareDocumentContents(string $documentId, Request $request, Context $context): Response
    {
        $deepLinkCode = $request->query->get('deepLinkCode');

        // This is a placeholder for Pickware document integration
        // In a real implementation, you would integrate with Pickware API
        $pdfContent = base64_decode('JVBERi0xLjQKJdPr6eEKMSAwIG9iago8PAovVHlwZSAvQ2F0YWxvZwovUGFnZXMgMiAwIFIKPj4KZW5kb2JqCjIgMCBvYmoKPDwKL1R5cGUgL1BhZ2VzCi9LaWRzIFszIDAgUl0KL0NvdW50IDEKPD4KZW5kb2JqCjMgMCBvYmoKPDwKL1R5cGUgL1BhZ2UKL1BhcmVudCAyIDAgUgovTWVkaWFCb3ggWzAgMCA2MTIgNzkyXQo+PgplbmRvYmoKeHJlZgowIDQKMDAwMDAwMDAwMCA2NTUzNSBmIAowMDAwMDAwMDA5IDAwMDAwIG4gCjAwMDAwMDAwNTggMDAwMDAgbiAKMDAwMDAwMDExNSAwMDAwMCBuIAp0cmFpbGVyCjw8Ci9TaXplIDQKL1Jvb3QgMSAwIFIKPj4Kc3RhcnR4cmVmCjE3NAolJUVPRgo=');

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"'
        ]);
    }
}