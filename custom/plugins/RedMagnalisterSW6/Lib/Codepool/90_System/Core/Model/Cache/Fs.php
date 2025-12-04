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
 * (c) 2010 - 2024 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLFilesystem::gi()->loadClass("model_cache_abstract");
/**
 * A filesystem driver class for the cache system.
 * @todo use MLHelper::getFilesystemInstance();
 */
class ML_Core_Model_Cache_Fs  extends ML_Core_Model_Cache_Abstract {
    /**
     * Creates an instance of this class
     * @return self
     */
    public function __construct() {
        if (!file_exists($this->getFilePath())) {
            @mkdir($this->getFilePath(), 0777, true);
        }
    }
    
    /**
     * Set a cache value.
     * @param string $sKey
     *    Cache id
     * @param mixed $mValue
     *    Value that will be cached
     * @param int $iLifeTime
     *    Life time in seconds. Lifetime 0 means the cach is always valid
     * @return self
     */
    public function set($sKey, $sContent, $iLifetime) {
        // $sContent = json_encode($mContent) ;
        $sFilename = $this->getFilePath($sKey);
        if (file_exists($sFilename)) {
            unlink($sFilename);
        }
        $sDir = dirname($sFilename);
        if (!file_exists($sDir)) {
            @mkdir($sDir, 0777, true);
        }
        if ($iLifetime !== 0) {
            $sContent .= "\n" . (time() + $iLifetime);
        } else {
            $sContent .= "\n" . $iLifetime;
        }
        if (file_put_contents($sFilename, $sContent) === false && file_exists($sFilename)) {
            unlink($sFilename);
        }
        return $this;
    }
    
    /**
     * Get a value from the cache using a cache id.
     * @param string $sKey
     * @return mixed
     * @throws ML_Filesystem_Exception
     *    In case the cache is too old or the cache id does not exist.
     */
    public function get($sKey) {
        $sFilename = $this->getFilePath($sKey);
        if (file_exists($sFilename)) {
            $sContent = file_get_contents($sFilename);
            $aContent = explode("\n", $sContent);
            if (is_array($aContent)) {
                $sExpirationDate = null;
                if(is_numeric(end($aContent))){
                    // for filesystem files which do not have lifetime set
                    $sExpirationDate = array_pop($aContent);
                    $sData = implode("\n", $aContent);
                } else {
                    // for filesystem files which do not have lifetime set
                    $sData = $sContent;
                }
                if (
                    isset($sExpirationDate)
                    && $sExpirationDate < time()
                    && $sExpirationDate !== '0'
                    && file_exists($sFilename)
                ) {
                    unlink($sFilename);
                    throw new ML_Filesystem_Exception("This cache key is too old: $sFilename");
                }

                return $sData;
            }
        } else {
            throw new ML_Filesystem_Exception("This cache key does not exist: $sFilename");
        }
    }

    /**
     * Get information of expiration
     * @param string $sKey
     * @return array
     */
    public function getInfo($sKey) {
        $sFilename = $this->getFilePath($sKey);
        $blFileExists = false;
        $blExpired = true;
        $mContent = null;
        $mModifiedTime = null;
        $mCreatedTime = null;
        $sExpirationDate = null;
        $sExpirationDateFormatted = null;
        $iTime = time();

        if (file_exists($sFilename)) {
            $blFileExists = true;
            $mModifiedTime = filemtime($sFilename);
            $mCreatedTime = filectime($sFilename);

            // Read file content and check for expiration timestamp in last line
            $sRawContent = file_get_contents($sFilename);
            $aContent = explode("\n", $sRawContent);

            if (is_array($aContent) && count($aContent) > 0) {
                $sLastLine = end($aContent);

                // Check if last line is numeric (expiration timestamp)
                if (is_numeric($sLastLine)) {
                    $sExpirationDate = $sLastLine;

                    // Remove expiration line from content
                    array_pop($aContent);
                    $mContent = implode("\n", $aContent);

                    // Check if expired
                    if ($sExpirationDate === '0') {
                        // Forever valid
                        $blExpired = false;
                        $sExpirationDateFormatted = 'Never (Forever)';
                    } elseif ($sExpirationDate < $iTime) {
                        // Expired
                        $blExpired = true;
                        $sExpirationDateFormatted = date('Y-m-d H:i:s', $sExpirationDate);
                    } else {
                        // Still valid
                        $blExpired = false;
                        $sExpirationDateFormatted = date('Y-m-d H:i:s', $sExpirationDate);
                    }
                } else {
                    // No expiration timestamp found, assume forever valid
                    $mContent = $sRawContent;
                    $blExpired = false;
                    $sExpirationDateFormatted = 'Never (No timestamp)';
                }
            } else {
                $mContent = $sRawContent;
            }
        }

        return array(
            'sKey'                      => $sKey,
            'blFileExists'              => $blFileExists,
            'blExpired'                 => $blExpired,
            'mContent'                  => $mContent,
            'mModifiedTime'             => array($mModifiedTime, date('Y-m-d H:i:s', $mModifiedTime)),
            'mCreatedTime'              => array($mCreatedTime, date('Y-m-d H:i:s', $mCreatedTime)),
            'sExpirationDate'           => $sExpirationDate,
            'sExpirationDateFormatted'  => $sExpirationDateFormatted,
        );
    }

    /**
     * Checks if a cache id exists.
     * @param string $sKey
     * @return bool
     */
    public function exists($sKey) {
        try {
            $this->get($sKey);
            return true;
        }
        catch (Exception $oEx) {
            return false;
        }
    }
    
    /**
     * Delete a cache id from the cache.
     * @param string $sKey
     * return ML_Magnalister_Model_Cache_Abstract
     *    A list of deleted keys
     */
    public function delete($sKey) {
        $filename = $this->getFilePath($sKey);
        if (file_exists($filename) && !is_dir($filename)) {
            unlink($filename);
        }
        return $this;
    }
    
    /**
     * Checks the availabillty of the cache driver. Here it returns always true.
     * @return bool
     */
    public function checkAvailablity() {
        return true;
    }
    
    /**
     * Returns the file path of a cache id.
     * @param string $sKey
     * @return string
     */
    protected function getFilePath($sKey = '') {
        return MLFilesystem::getCachePath($sKey);
    }

    /**
     * Flushes the cache.
     *
     * @param string $pattern
     * @return $this
     */
    public function flush($pattern = '*') {
        foreach (MLFilesystem::gi()->glob($this->getFilePath($pattern)) as $sFile) {
            $this->delete(basename($sFile));
        }
        return $this;
    }
    
    /**
     * Get a list of all cached cache ids.
     * @return array
     */
    public function getList() {
        $aFileList = array();
        foreach (MLFilesystem::gi()->glob($this->getFilePath('*')) as $sFile) {
            if (!is_dir($sFile)) {
                $aFileList[] = basename($sFile);
            }
        }
        return $aFileList;
    }
    
}
