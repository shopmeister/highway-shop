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

use Redgecko\Magnalister\Controller\MagnalisterController;

MLFilesystem::gi()->loadClass("core_model_cache_fs");

/**
 * A filesystem driver class for the cache system.
 * @todo use MLHelper::getFilesystemInstance();
 */
class ML_Shopware6_Helper_Model_Cache_Filesystem extends ML_Core_Model_Cache_Fs {

    protected $sMagnalisterDirectoryName = 'RedMagnalister';
    private static $isFilesystemAccessible = null;

    /**
     * Creates an instance of this class
     * @return self
     */
    public function __construct() {
        if (self::$isFilesystemAccessible === null) {
            $this->setFilesystemAccessibility($this->checkFilesystemAccessibility());
        }
        $this->createDirectory();
    }

    private function checkFilesystemAccessibility(): bool {
        try {
            $sTestFileName = 'test.lock';
            $sFilename = $this->getFilePath($sTestFileName);
            $this->writeIntoFile($sFilename, 'test');
            if ($this->exists($sTestFileName)) {
                $this->delete($sTestFileName);
                return true;
            }
        } catch (\Exception $ex) {

        }

        return false;
    }

    public function setFilesystemAccessibility($isAccessible) {
        self::$isFilesystemAccessible = $isAccessible;
    }

    public function isFilesystemAccessible() {
        return self::$isFilesystemAccessible;
    }

    protected function createDirectory(): void {
        if ($this->isFilesystemAccessible() && !MagnalisterController::getFileSystem()->has($this->sMagnalisterDirectoryName)) {
            if (method_exists(MagnalisterController::getFileSystem(), 'createDir')) {//6.4
                MagnalisterController::getFileSystem()->createDir($this->sMagnalisterDirectoryName);
            } else {//6.5
                MagnalisterController::getFileSystem()->createDirectory($this->sMagnalisterDirectoryName);
            }
        } else {
            if (!file_exists($this->sMagnalisterDirectoryName)) {
                @mkdir($this->sMagnalisterDirectoryName, 0777, true);
            }
        }
    }

    /**
     * Set a cache value.
     * @param string $sKey
     *    Cache id
     * @param mixed $sContent
     *    Value that will be cached
     * @param int $iLifetime
     *    Lifetime in seconds
     * @return self
     */
    public function set($sKey, $sContent, $iLifetime) {
        if ($this->isFilesystemAccessible()) {
            $sFilename = $this->getFilePath($sKey);
            $sContent .= "\n" . (time() + $iLifetime);
            if (MagnalisterController::getFileSystem()->has($sFilename)) {
                MagnalisterController::getFileSystem()->delete($sFilename);
            }
            $sDir = dirname($sFilename);
            $this->fileOrDirectoryExists($sDir);

            $writeResult = $this->writeIntoFile($sFilename, $sContent);
            if (!$writeResult && (MagnalisterController::getFileSystem()->has($sFilename))) {
                MagnalisterController::getFileSystem()->delete($sFilename);
            }
            return $this;
        } else {
           return parent::set($sKey, $sContent, $iLifetime);
        }
    }

    /**
     * Returns the file path of a cache id.
     * @param string $sKey
     * @return string
     */
    protected function getFilePath($sKey = '') {
        return $this->sMagnalisterDirectoryName . '/' . $sKey;
    }

    /**
     * Delete a cache id from the cache.
     * @param string $sKey
     * return ML_Magnalister_Model_Cache_Abstract
     *    A list of deleted keys
     */
    public function delete($sKey) {
        if ($this->isFilesystemAccessible()) {
            $sFilename = $this->getFilePath($sKey);
            MagnalisterController::getFileSystem()->delete($sFilename);
            // we had issue with lock files during order import the lock file was not removed with delete function
            // in that case we empty the file content
            if (MagnalisterController::getFileSystem()->has($sFilename)) {
                MagnalisterController::getFileSystem()->write($sFilename, '');
            }
            return $this;
        } else {
            return parent::delete($sKey);
        }
    }

    /**
     * Checks if a cache id exists.
     * We had issue with removing the lock files in order import (ML_Amazon_Model_Service_ImportOrders.lock)
     * we updated the function to check if file exists and if it is empty because if deletion is not complete
     * we remove the contents of the file
     * @param string $sKey
     * @return bool
     */
    public function exists($sKey) {
        if ($this->isFilesystemAccessible()) {
            $sFilename = $this->getFilePath($sKey);
            if (MagnalisterController::getFileSystem()->has($sFilename) && !empty(MagnalisterController::getFileSystem()->read($sFilename))) {
                return true;
            } else {
                return false;
            }
        } else {
            return parent::exists($sKey);
        }
    }

    /**
     * Checks if a file or directory exists and creates it if it does not exist.
     * @param string $sDir The directory path to check or create.
     * @return void
     */
    protected function fileOrDirectoryExists(string $sDir): void {
        try {
            if (MagnalisterController::getFileSystem()->has($sDir)) {
                if (method_exists(MagnalisterController::getFileSystem(), 'createDir')) {//6.4
                    MagnalisterController::getFileSystem()->createDir($sDir);
                } else {//6.5
                    MagnalisterController::getFileSystem()->createDirectory($sDir);
                }
            }
        } catch (\Exception $ex) {
            MLMessage::gi()->addDebug('Error while checking if directory exists: ' . $ex);
        }
    }

    /**
     * Writes content into a specified file.
     * Attempts to use the appropriate file system method, depending on its capabilities.
     *
     * @param string $sFilename The name of the file to write to.
     * @param string $sContent The content to write into the file.
     * @return bool True if the write operation was successful, false otherwise.
     */
    protected function writeIntoFile(string $sFilename, string $sContent) {
        if (method_exists(MagnalisterController::getFileSystem(), 'put')) {//6.4
            $writeResult = MagnalisterController::getFileSystem()->put($sFilename, $sContent);
        } else {//6.5
            MagnalisterController::getFileSystem()->write($sFilename, $sContent);
            $writeResult = true;
        }
        return $writeResult;
    }

    /**
     * Retrieves the cached data associated with the given key.
     * If the cache key does not exist or has expired, an exception is thrown.
     *
     * @param string $sKey The key used to retrieve the cached data.
     * @return string The cached data if it exists and is not expired.
     * @throws ML_Filesystem_Exception If the cache key does not exist or has expired.
     */
    public function get($sKey) {
        if ($this->isFilesystemAccessible()) {
            $sFilename = $this->getFilePath($sKey);
            if (MagnalisterController::getFileSystem()->has($sFilename)) {
                $sContent = MagnalisterController::getFileSystem()->read($sFilename);
                $aContent = explode("\n", $sContent);
                if (is_array($aContent)) {
                    if (count($aContent) === 2) {
                        $sData = $aContent[0];
                        $sExpirationDate = (int)$aContent[1];
                        if ($sExpirationDate < time()) {
                            MagnalisterController::getFileSystem()->delete($sFilename);
                            throw new ML_Filesystem_Exception("This cache key is too old: $sFilename");
                        }
                        return $sData;
                    }
                }
            }
            throw new ML_Filesystem_Exception("This cache key does not exist: $sFilename");
        } else {
            return parent::get($sKey);
        }
    }

    /**
     * Get information of expiration
     * @param string $sKey
     * @return array
     */
    public function getInfo($sKey) {
        return array();
    }

    /**
     * Flushes the cache.
     * @ToDo $pattern should be implemented
     * @param string $pattern
     * @return $this
     */
    public function flush($pattern = '*') {
        if ($this->isFilesystemAccessible()) {
            foreach ($this->getList() as $sFile) {
                $this->delete(basename($sFile));
            }
            return $this;
        } else {
            return parent::flush($pattern);
        }

    }

    /**
     * Get a list of all cached cache ids.
     * @return array
     */
    public function getList() {
        $aFileList = array();
        if ($this->isFilesystemAccessible()) {
            try {
                foreach (MagnalisterController::getFileSystem()->listContents($this->sMagnalisterDirectoryName) as $aFile) {
                    $aFileList[] = substr($aFile['path'], strlen($this->sMagnalisterDirectoryName) + 1);
                }
            } catch (\Exception $ex) {
                MLMessage::gi()->addDebug('Error while getting the list of all files: ' . $ex);
            }
        } else {
            $aFileList = parent::getList();
        }
        return $aFileList;
    }

}
