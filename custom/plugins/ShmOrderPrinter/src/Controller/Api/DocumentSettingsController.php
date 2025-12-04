<?php declare(strict_types=1);

namespace Shm\OrderPrinter\Controller\Api;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Api\Response\JsonApiResponse;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
class DocumentSettingsController extends AbstractController
{
    private Connection $connection;
    private EntityRepositoryInterface $printerSettingsRepository;
    private LoggerInterface $logger;

    public function __construct(
        Connection $connection,
        EntityRepositoryInterface $printerSettingsRepository,
        LoggerInterface $logger
    ) {
        $this->connection = $connection;
        $this->printerSettingsRepository = $printerSettingsRepository;
        $this->logger = $logger;
    }

    #[Route(path: '/api/shm-kindsgut-documents-setting', name: 'api.shm.documents.settings.get', methods: ['GET'])]
    public function getSettings(Request $request, Context $context): JsonResponse
    {
        try {
            $filterType = $request->query->get('type', 'document_setting');

            $this->logger->info('[ShmOrderPrinter] Loading document settings', [
                'filter_type' => $filterType,
                'context' => 'DocumentSettingsController::getSettings'
            ]);

            // DBAL: Hole MASTER Settings (neueste nach updated_at) und bereinige Duplikate
            $masterSetting = $this->connection->fetchAssociative(
                'SELECT LOWER(HEX(id)) as id, setting, created_at, updated_at
                 FROM shm_printer_settings
                 WHERE JSON_EXTRACT(setting, "$.type") = ?
                 ORDER BY updated_at DESC, created_at DESC
                 LIMIT 1',
                [$filterType]
            );

            if (!$masterSetting) {
                return new JsonResponse(['data' => []]);
            }

            // OPTIONAL: Bereinige alte Duplikate (alle außer Master)
            $this->cleanupDuplicateSettings($filterType, $masterSetting['id']);

            // Dekodiere Settings und mache sie Frontend-kompatibel
            $settingData = json_decode($masterSetting['setting'], true);

            // KRITISCH: Erweitere Settings um alle verfügbaren Verkaufskanäle mit leeren Arrays
            // Das verhindert "undefined is not iterable" Fehler im Frontend
            $settingData = $this->ensureAllSalesChannelsInitialized($settingData);

            $data = [
                [
                    'id' => $masterSetting['id'],
                    'attributes' => [
                        'setting' => $settingData
                    ],
                    'meta' => [
                        'created_at' => $masterSetting['created_at'],
                        'updated_at' => $masterSetting['updated_at']
                    ]
                ]
            ];

            $this->logger->debug('[ShmOrderPrinter] Master settings loaded', [
                'master_id' => $masterSetting['id'],
                'sales_channels_count' => count(json_decode($masterSetting['setting'], true)['data'] ?? [])
            ]);

            return new JsonResponse(['data' => $data]);
        } catch (\Exception $e) {
            $this->logger->error('[ShmOrderPrinter] Failed to load settings', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return new JsonResponse([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/api/shm-kindsgut-documents-setting', name: 'api.shm.documents.settings.create', methods: ['POST'])]
    public function createSettings(Request $request, Context $context): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['setting'])) {
            return new JsonResponse(['error' => 'Missing setting data'], Response::HTTP_BAD_REQUEST);
        }

        $id = Uuid::randomHex();

        $this->printerSettingsRepository->create([
            [
                'id' => $id,
                'setting' => $data['setting']
            ]
        ], $context);

        return new JsonResponse(['id' => $id], Response::HTTP_CREATED);
    }

    #[Route(path: '/api/shm-kindsgut-documents-setting/{id}', name: 'api.shm.documents.settings.update', methods: ['PATCH'])]
    public function updateSettings(string $id, Request $request, Context $context): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['setting'])) {
            return new JsonResponse(['error' => 'Missing setting data'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Shopware 6.6 Update-Logic für Settings ohne Versionierung
            // 1. Prüfen ob Setting existiert
            $criteria = new Criteria([$id]);
            $existing = $this->printerSettingsRepository->search($criteria, $context);

            if ($existing->getTotal() === 0) {
                // Erstellen wenn nicht vorhanden
                $this->printerSettingsRepository->create([
                    [
                        'id' => $id,
                        'setting' => $data['setting']
                    ]
                ], $context);

                return new JsonResponse(['id' => $id], Response::HTTP_CREATED);
            } else {
                // Update mit expliziter ID (ohne Versionierung)
                $this->printerSettingsRepository->update([
                    [
                        'id' => $id,
                        'setting' => $data['setting']
                    ]
                ], $context);

                return new JsonResponse(null, Response::HTTP_NO_CONTENT);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/api/shm-kindsgut-documents-setting/upsert', name: 'api.shm.documents.settings.upsert', methods: ['POST'])]
    public function upsertSettings(Request $request, Context $context): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['setting'])) {
            return new JsonResponse(['error' => 'Missing setting data'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Shopware Logger für Debug-Informationen
            $this->logger->info('[ShmOrderPrinter] Document Settings Upsert started', [
                'context' => 'DocumentSettingsController::upsertSettings',
                'payload_size' => strlen(json_encode($data)),
                'has_setting_data' => isset($data['setting'])
            ]);

            // Settings-Type aus dem Payload extrahieren (z.B. "document_setting")
            $settingType = $data['setting']['type'] ?? 'document_setting';
            $settingJson = json_encode($data['setting']);

            $this->logger->debug('[ShmOrderPrinter] Processing settings upsert', [
                'setting_type' => $settingType,
                'sales_channels_count' => isset($data['setting']['data']) ? count($data['setting']['data']) : 0,
                'sales_channels' => isset($data['setting']['data']) ? array_keys($data['setting']['data']) : []
            ]);

            // DBAL: Prüfe ob Setting mit diesem Type bereits existiert (neuestes nach updated_at)
            $existingId = $this->connection->fetchOne(
                'SELECT LOWER(HEX(id)) as id FROM shm_printer_settings
                 WHERE JSON_EXTRACT(setting, "$.type") = ?
                 ORDER BY updated_at DESC, created_at DESC
                 LIMIT 1',
                [$settingType]
            );

            if ($existingId) {
                // DBAL: Update bestehende Settings
                $result = $this->connection->executeStatement(
                    'UPDATE shm_printer_settings
                     SET setting = ?, updated_at = NOW(3)
                     WHERE LOWER(HEX(id)) = ?',
                    [$settingJson, $existingId]
                );

                $this->logger->info('[ShmOrderPrinter] Settings updated successfully', [
                    'existing_id' => $existingId,
                    'rows_affected' => $result,
                    'setting_type' => $settingType
                ]);

                return new JsonResponse(['id' => $existingId, 'action' => 'updated'], Response::HTTP_OK);
            } else {
                // DBAL: Erstelle neue Settings
                $id = Uuid::randomHex();
                $result = $this->connection->executeStatement(
                    'INSERT INTO shm_printer_settings (id, setting, created_at, updated_at)
                     VALUES (UNHEX(?), ?, NOW(3), NOW(3))',
                    [str_replace('-', '', $id), $settingJson]
                );

                $this->logger->info('[ShmOrderPrinter] Settings created successfully', [
                    'new_id' => $id,
                    'rows_affected' => $result,
                    'setting_type' => $settingType
                ]);

                return new JsonResponse(['id' => $id, 'action' => 'created'], Response::HTTP_CREATED);
            }
        } catch (\Exception $e) {
            $this->logger->error('[ShmOrderPrinter] Settings upsert failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'setting_type' => $settingType ?? 'unknown'
            ]);

            return new JsonResponse([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/api/shm-kindsgut-documents-setting/{id}', name: 'api.shm.documents.settings.delete', methods: ['DELETE'])]
    public function deleteSettings(string $id, Context $context): JsonResponse
    {
        try {
            $this->printerSettingsRepository->delete([
                ['id' => $id]
            ], $context);

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bereinige Duplikat-Settings und behalte nur den Master-Eintrag
     */
    private function cleanupDuplicateSettings(string $settingType, string $masterSettingId): void
    {
        try {
            // Zähle alle Settings mit diesem Type
            $totalCount = $this->connection->fetchOne(
                'SELECT COUNT(*) FROM shm_printer_settings WHERE JSON_EXTRACT(setting, "$.type") = ?',
                [$settingType]
            );

            if ($totalCount <= 1) {
                return; // Keine Duplikate vorhanden
            }

            // Lösche alle außer dem Master
            $deletedCount = $this->connection->executeStatement(
                'DELETE FROM shm_printer_settings
                 WHERE JSON_EXTRACT(setting, "$.type") = ?
                 AND LOWER(HEX(id)) != ?',
                [$settingType, $masterSettingId]
            );

            if ($deletedCount > 0) {
                $this->logger->info('[ShmOrderPrinter] Cleaned up duplicate settings', [
                    'setting_type' => $settingType,
                    'master_id' => $masterSettingId,
                    'deleted_count' => $deletedCount,
                    'total_before' => $totalCount
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->warning('[ShmOrderPrinter] Failed to cleanup duplicates', [
                'error' => $e->getMessage(),
                'setting_type' => $settingType
            ]);
        }
    }

    /**
     * Stelle sicher, dass alle Verkaufskanäle in den Settings existieren (mit leeren Arrays)
     * Verhindert "undefined is not iterable" Fehler im Frontend
     */
    private function ensureAllSalesChannelsInitialized(array $settingData): array
    {
        try {
            // Hole alle verfügbaren Verkaufskanäle aus der Datenbank
            $allSalesChannels = $this->connection->fetchAllAssociative(
                'SELECT LOWER(HEX(id)) as id FROM sales_channel WHERE active = 1'
            );

            // Initialisiere data array falls nicht vorhanden
            if (!isset($settingData['data'])) {
                $settingData['data'] = [];
            }

            // Stelle sicher, dass jeder Verkaufskanal ein Array hat
            foreach ($allSalesChannels as $channel) {
                $channelId = $channel['id'];
                if (!isset($settingData['data'][$channelId])) {
                    $settingData['data'][$channelId] = [];
                }
            }

            $this->logger->debug('[ShmOrderPrinter] Initialized sales channels', [
                'total_channels' => count($allSalesChannels),
                'initialized_channels' => count($settingData['data'])
            ]);

            return $settingData;
        } catch (\Exception $e) {
            $this->logger->warning('[ShmOrderPrinter] Failed to initialize sales channels', [
                'error' => $e->getMessage()
            ]);

            // Fallback: Gib ursprüngliche Daten zurück
            return $settingData;
        }
    }
}