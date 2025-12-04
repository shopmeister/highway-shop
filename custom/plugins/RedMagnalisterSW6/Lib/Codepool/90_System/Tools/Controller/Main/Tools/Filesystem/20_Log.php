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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLFilesystem::gi()->loadClass('Core_Controller_Abstract');

class ML_Tools_Controller_Main_Tools_Filesystem_Log extends ML_Core_Controller_Abstract {
    
    protected $aParameters = array('controller');
    
    protected $aFileList = null;

    protected $aOldFileList = null;

    protected $aContents = null;
    
    protected function getFileList() {
        if ($this->aFileList === null) {
            $this->aFileList = array();
            foreach (MLFilesystem::glob(MLFilesystem::getLogPath() . '/*.*') as $sFile) {
                $this->aFileList[] = pathinfo($sFile, PATHINFO_FILENAME);
            }
        }
        return $this->aFileList;
    }
    
    protected function getOldContents() {
        $sLogfile = MLRequest::gi()->data('logfile');
        if (
            empty($sLogfile)
            || !in_array($sLogfile, $this->getFileList())
        ) {
            return false;
        } else {
            if ($this->aOldFileList === null) {
                $this->aOldFileList = array();
                foreach (MLFilesystem::glob(MLFilesystem::getLogPath() . '/old/' . pathinfo($sLogfile, PATHINFO_FILENAME) . '_*.log.gz') as $sFile) {
                    $this->aOldFileList[] = basename($sFile);
                }
            }
            return $this->aOldFileList;
        }
    }

    protected function getContents()
    {
        $sZip = MLRequest::gi()->data('Zip');
        $sLogfile = MLRequest::gi()->data('logfile');
        $aPatterns = [];
        foreach (['pattern1', 'pattern2', 'pattern3'] as $patternKey) {
            $sPattern = trim(MLHelper::getPHP8Compatibility()->checkNull(MLRequest::gi()->data($patternKey)));
            if (!empty($sPattern)) {
                try {
                    ob_start();
                    if (preg_match($sPattern, 'ERROR_CHECK') === false) {
                        $sPattern = '/' . preg_quote($sPattern) . '/';
                        MLMessage::gi()->addDebug('No regex-pattern. Pattern changed to `' . $sPattern . '`.');
                    }
                    $warning = ob_get_clean();
                    if (!empty($warning)) {
                        MLMessage::gi()->addDebug($patternKey . ' - ' . $sPattern . ' - ' . $warning);
                    }
                } catch (\Exception $ex) {
                    $sPattern = '/' . preg_quote($sPattern) . '/';
                    MLMessage::gi()->addDebug('No regex-pattern. Pattern changed to `' . $sPattern . '`.');
                }
                $aPatterns[] = $sPattern;
            }
        }
        if (
            empty($sLogfile)
            || (empty($aPatterns) && empty($sZip))
            || !in_array($sLogfile, $this->getFileList())
        ) {
            return false;
        } else {
            if ($this->aContents === null) {
                if ($sZip !== null) {
                    $this->aContents = basename(MLLog::gi()->getZip($sLogfile));
                } else {
                    $this->aContents = array();
                    $aLines = MLLog::gi()->getFile($sLogfile, false);
                    foreach ($aPatterns as $sPattern) {
                        $matchedLines = [];
                        foreach ($aLines as $sLine) {
                            if (preg_match($sPattern, $sLine)) {
                                $matchedLines[]= $sLine;                                
                            }
                        }
                        $aLines = $matchedLines;
                    }

                    foreach ($aLines as $sLine) {
                        $sInfo = substr($sLine, 0, strpos($sLine, '{') - 1);
                        $sJson = substr($sLine, strpos($sLine, '{'), strrpos($sLine, '}'));
                        $this->aContents[] = array(
                            'date' => trim(substr($sInfo, 0, strpos($sLine, '(') - 1)),
                            'build' => trim(preg_replace('/.*\\(Build\\:(.*)\\).*/Uis', '$1', $sInfo)),
                            'data' => $sJson
                        );
                    }
                }
            }
            return $this->aContents;
        }
    }

}