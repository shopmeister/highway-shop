<?php

MLFilesystem::gi()->loadClass('Base_Model_Table_Image');

class ML_Shopware6_Model_Table_Image extends ML_Base_Model_Table_Image
{
    // Add any properties or methods specific to Shopware6

    protected function fileCTime($filePath)
    {
        // To prevent big request times to CDNs we only check the file size
        return 0;
    }

    protected function fileExists($filePath)
    {
        return \Redgecko\Magnalister\Controller\MagnalisterController::getFilesystem()->has($filePath);
    }

    protected function fileMTime($filePath)
    {
        // To prevent big request times to CDNs we only check the file size
        return 0;
    }

    protected function fileSize($filePath)
    {
        $filesystem = \Redgecko\Magnalister\Controller\MagnalisterController::getFilesystem();

        // The API v1 has the method getSize instead of fileSize
        if (method_exists($filesystem, 'getSize')) {
            return $filesystem->getSize($filePath);
        }

        return $filesystem->fileSize($filePath);
    }
}
