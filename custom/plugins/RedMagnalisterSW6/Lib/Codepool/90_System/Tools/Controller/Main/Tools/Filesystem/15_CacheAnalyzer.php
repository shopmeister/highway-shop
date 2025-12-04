<?php
/*
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * (c) 2010 - 2025 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLFilesystem::gi()->loadClass('Core_Controller_Abstract');

class ML_Tools_Controller_Main_Tools_Filesystem_CacheAnalyzer extends ML_Core_Controller_Abstract {

    protected $aParameters = array('controller');

    protected $aCacheList = null;
    protected $aCacheData = null;
    protected $aFilteredList = null;

    /**
     * Get the complete list of cache keys
     * @return array
     */
    protected function getCacheList() {
        if ($this->aCacheList === null) {
            $this->aCacheList = MLCache::gi()->getList();
        }
        return $this->aCacheList;
    }

    /**
     * Get filtered and searched cache list
     * @return array
     */
    protected function getFilteredCacheList() {
        if ($this->aFilteredList !== null) {
            return $this->aFilteredList;
        }

        $aList = $this->getCacheList();
        $sSearch = MLRequest::gi()->data('search');

        // Apply search filter
        if (!empty($sSearch)) {
            $aList = array_filter($aList, function($sKey) use ($sSearch) {
                return stripos($sKey, $sSearch) !== false;
            });
        }

        $this->aFilteredList = array_values($aList);
        return $this->aFilteredList;
    }

    /**
     * Get detailed information for cache files with pagination
     * @return array
     */
    public function getCacheData() {
        if ($this->aCacheData !== null) {
            return $this->aCacheData;
        }

        $aFilteredList = $this->getFilteredCacheList();
        $iTotalCount = count($aFilteredList);

        // Pagination parameters
        $iPage = max(1, (int)MLRequest::gi()->data('page', 1));
        $iPerPage = max(25, min(200, (int)MLRequest::gi()->data('perpage', 25)));
        $iOffset = ($iPage - 1) * $iPerPage;

        // Filter parameters
        $sFilterExpired = MLRequest::gi()->data('filter_expired'); // 'yes', 'no', or null for all
        $sFilterDateFrom = MLRequest::gi()->data('filter_date_from');
        $sFilterDateTo = MLRequest::gi()->data('filter_date_to');
        $iFilterSizeMin = (int)MLRequest::gi()->data('filter_size_min', 0);
        $iFilterSizeMax = (int)MLRequest::gi()->data('filter_size_max', 0);
        $sFilterKeyPattern = MLRequest::gi()->data('filter_key_pattern'); // Filter for specific key patterns

        // Sorting parameters
        $sSortBy = MLRequest::gi()->data('sortby', 'created'); // 'key', 'created', 'size', 'expired', 'expires'
        $sSortOrder = MLRequest::gi()->data('sortorder', 'desc'); // 'asc' or 'desc'

        // Collect detailed data for all filtered items (needed for filtering and sorting)
        $aAllData = array();
        foreach ($aFilteredList as $sKey) {
            try {
                $aInfo = MLCache::gi()->getInfo($sKey);
                $sFilePath = MLFilesystem::getCachePath($sKey);
                $iFileSize = file_exists($sFilePath) ? filesize($sFilePath) : 0;

                // Apply filters
                if ($sFilterExpired === 'yes' && !$aInfo['blExpired']) {
                    continue;
                }
                if ($sFilterExpired === 'no' && $aInfo['blExpired']) {
                    continue;
                }

                if (!empty($sFilterDateFrom)) {
                    $iFilterDateFrom = strtotime($sFilterDateFrom);
                    if ($aInfo['mCreatedTime'][0] < $iFilterDateFrom) {
                        continue;
                    }
                }

                if (!empty($sFilterDateTo)) {
                    $iFilterDateTo = strtotime($sFilterDateTo . ' 23:59:59');
                    if ($aInfo['mCreatedTime'][0] > $iFilterDateTo) {
                        continue;
                    }
                }

                if ($iFilterSizeMin > 0 && $iFileSize < $iFilterSizeMin) {
                    continue;
                }

                if ($iFilterSizeMax > 0 && $iFileSize > $iFilterSizeMax) {
                    continue;
                }

                // Apply key pattern filter (e.g., to exclude ML_FILESYSTEM)
                if (!empty($sFilterKeyPattern)) {
                    if ($sFilterKeyPattern === 'exclude_ml_filesystem') {
                        if (stripos($sKey, 'MLFILESYSTEM') === 0) {
                            continue;
                        }
                    }
                }

                $aAllData[] = array(
                    'sKey' => $sKey,
                    'blFileExists' => $aInfo['blFileExists'],
                    'blExpired' => $aInfo['blExpired'],
                    'mContent' => $aInfo['mContent'],
                    'mCreatedTime' => $aInfo['mCreatedTime'],
                    'sExpirationDate' => $aInfo['sExpirationDate'],
                    'sExpirationDateFormatted' => $aInfo['sExpirationDateFormatted'],
                    'iFileSize' => $iFileSize,
                    'sFilePath' => $sFilePath
                );
            } catch (Exception $oEx) {
                // Skip files that cannot be read
                continue;
            }
        }

        // Sort the data
        usort($aAllData, function($a, $b) use ($sSortBy, $sSortOrder) {
            $iResult = 0;
            switch ($sSortBy) {
                case 'key':
                    $iResult = strcmp($a['sKey'], $b['sKey']);
                    break;
                case 'created':
                    $iResult = $a['mCreatedTime'][0] - $b['mCreatedTime'][0];
                    break;
                case 'size':
                    $iResult = $a['iFileSize'] - $b['iFileSize'];
                    break;
                case 'expired':
                    $iResult = ($a['blExpired'] ? 1 : 0) - ($b['blExpired'] ? 1 : 0);
                    break;
                case 'expires':
                    // Sort by expiration date
                    $aExpA = $a['sExpirationDate'] !== null ? $a['sExpirationDate'] : PHP_INT_MAX;
                    $aExpB = $b['sExpirationDate'] !== null ? $b['sExpirationDate'] : PHP_INT_MAX;
                    // Treat '0' (forever) as max value so they sort last
                    $aExpA = ($aExpA === '0') ? PHP_INT_MAX : $aExpA;
                    $aExpB = ($aExpB === '0') ? PHP_INT_MAX : $aExpB;
                    $iResult = $aExpA - $aExpB;
                    break;
            }
            return $sSortOrder === 'asc' ? $iResult : -$iResult;
        });

        $iFilteredCount = count($aAllData);
        $iTotalPages = ceil($iFilteredCount / $iPerPage);

        // Extract paginated subset
        $aPageData = array_slice($aAllData, $iOffset, $iPerPage);

        $this->aCacheData = array(
            'data' => $aPageData,
            'pagination' => array(
                'total_count' => $iTotalCount,
                'filtered_count' => $iFilteredCount,
                'page' => $iPage,
                'per_page' => $iPerPage,
                'total_pages' => $iTotalPages,
                'offset' => $iOffset
            ),
            'filters' => array(
                'search' => MLRequest::gi()->data('search'),
                'expired' => $sFilterExpired,
                'date_from' => $sFilterDateFrom,
                'date_to' => $sFilterDateTo,
                'size_min' => $iFilterSizeMin,
                'size_max' => $iFilterSizeMax,
                'key_pattern' => $sFilterKeyPattern
            ),
            'sorting' => array(
                'sortby' => $sSortBy,
                'sortorder' => $sSortOrder
            )
        );

        return $this->aCacheData;
    }

    /**
     * Export cache data as CSV
     * @return void
     */
    public function exportCSV() {
        if (MLRequest::gi()->data('export') !== 'csv') {
            return false;
        }

        // Get all cache data without pagination
        $aData = $this->getCacheData();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=cache_analysis_' . date('Y-m-d_H-i-s') . '.csv');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, array(
            'Cache Key',
            'File Exists',
            'Expired',
            'File Size (bytes)',
            'Created Time',
            'Expires',
            'File Path',
            'Content Preview'
        ));

        // CSV data - export all filtered data
        $aFilteredList = $this->getFilteredCacheList();
        foreach ($aFilteredList as $sKey) {
            try {
                $aInfo = MLCache::gi()->getInfo($sKey);
                $sFilePath = MLFilesystem::getCachePath($sKey);
                $iFileSize = file_exists($sFilePath) ? filesize($sFilePath) : 0;
                $sContentPreview = substr($aInfo['mContent'], 0, 100);

                fputcsv($output, array(
                    $sKey,
                    $aInfo['blFileExists'] ? 'Yes' : 'No',
                    $aInfo['blExpired'] ? 'Yes' : 'No',
                    $iFileSize,
                    $aInfo['mCreatedTime'][1],
                    isset($aInfo['sExpirationDateFormatted']) ? $aInfo['sExpirationDateFormatted'] : 'Unknown',
                    $sFilePath,
                    $sContentPreview
                ));
            } catch (Exception $oEx) {
                continue;
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Export cache data as JSON
     * @return void
     */
    public function exportJSON() {
        if (MLRequest::gi()->data('export') !== 'json') {
            return false;
        }

        $aExportData = array();
        $aFilteredList = $this->getFilteredCacheList();

        foreach ($aFilteredList as $sKey) {
            try {
                $aInfo = MLCache::gi()->getInfo($sKey);
                $sFilePath = MLFilesystem::getCachePath($sKey);
                $iFileSize = file_exists($sFilePath) ? filesize($sFilePath) : 0;

                $aExportData[] = array(
                    'cache_key' => $sKey,
                    'file_exists' => $aInfo['blFileExists'],
                    'expired' => $aInfo['blExpired'],
                    'file_size' => $iFileSize,
                    'created_time' => $aInfo['mCreatedTime'][1],
                    'created_timestamp' => $aInfo['mCreatedTime'][0],
                    'expiration_date' => isset($aInfo['sExpirationDateFormatted']) ? $aInfo['sExpirationDateFormatted'] : 'Unknown',
                    'expiration_timestamp' => $aInfo['sExpirationDate'],
                    'file_path' => $sFilePath,
                    'content' => $aInfo['mContent']
                );
            } catch (Exception $oEx) {
                continue;
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=cache_analysis_' . date('Y-m-d_H-i-s') . '.json');

        echo json_encode($aExportData, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Delete selected cache entries
     * @return void
     */
    public function deleteSelected() {
        if (MLRequest::gi()->data('delete_selected') === null) {
            return false;
        }

        $aSelected = MLRequest::gi()->data('selected', array());
        $iDeleted = 0;

        foreach ($aSelected as $sKey) {
            try {
                MLCache::gi()->delete($sKey);
                $iDeleted++;
            } catch (Exception $oEx) {
                MLMessage::gi()->addError('Error deleting cache key: ' . $sKey);
            }
        }

        if ($iDeleted > 0) {
            MLMessage::gi()->addInfo('Successfully deleted ' . $iDeleted . ' cache entries.');
        }

        return true;
    }

    /**
     * Delete all expired cache entries
     * @return void
     */
    public function deleteExpired() {
        if (MLRequest::gi()->data('delete_expired') === null) {
            return false;
        }

        $aList = $this->getCacheList();
        $iDeleted = 0;

        foreach ($aList as $sKey) {
            try {
                $aInfo = MLCache::gi()->getInfo($sKey);
                if ($aInfo['blExpired']) {
                    MLCache::gi()->delete($sKey);
                    $iDeleted++;
                }
            } catch (Exception $oEx) {
                continue;
            }
        }

        if ($iDeleted > 0) {
            MLMessage::gi()->addInfo('Successfully deleted ' . $iDeleted . ' expired cache entries.');
        } else {
            MLMessage::gi()->addInfo('No expired cache entries found.');
        }

        return true;
    }

    /**
     * Constructor - handle actions
     */
    public function __construct() {
        parent::__construct();

        // Handle export actions
        $this->exportCSV();
        $this->exportJSON();

        // Handle delete actions
        $this->deleteSelected();
        $this->deleteExpired();
    }
}
