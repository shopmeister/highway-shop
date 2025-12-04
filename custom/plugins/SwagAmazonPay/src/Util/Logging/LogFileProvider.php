<?php

declare(strict_types=1);


namespace Swag\AmazonPay\Util\Logging;

use Exception;
use League\Flysystem\FilesystemOperator;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class LogFileProvider implements LogFileProviderInterface
{
    private const DIRECTORY_NAME = 'swag-amazon-pay';

    private string $logDirectory;

    private string $filePattern;

    private $fileSystem;

    public function __construct(
        string $logDirectory,
        string $filePattern,
        FilesystemOperator $fileSystem
    ) {
        $this->logDirectory = $logDirectory;
        $this->filePattern = $filePattern;
        $this->fileSystem = $fileSystem;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogFileList(): array
    {
        if (!\is_dir($this->logDirectory)) {
            return [];
        }

        $result = \scandir($this->logDirectory, \SCANDIR_SORT_DESCENDING);

        if ($result === false) {
            return [];
        }

        $result = \array_filter($result, function ($file): bool {
            if (\mb_strpos($file, $this->filePattern) !== false) {
                return true;
            }

            return false;
        });

        return \array_values($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsolutePath(string $fileName): string
    {
        // Return the absolute path ONLY for swag_amazon_pay log files to prevent path manipulation and exploits.
        if (\mb_strpos($fileName, $this->filePattern) === false || !\in_array($fileName, $this->getLogFileList(), true)) {
            throw new \RuntimeException('The requested file does not match the file pattern or could not be found.');
        }

        return \sprintf('%s/%s', $this->logDirectory, $fileName);
    }

    /**
     * {@inheritdoc}
     */
    public function compressLogFiles(array $files): string
    {
        $outputDir = \sprintf('%s/%s.zip', self::DIRECTORY_NAME, Uuid::randomHex());
        $zipFileNameTemp = \sprintf('%s/%s.zip', $this->logDirectory, Uuid::randomHex());

        $zipArchive = new \ZipArchive();

        if ($zipArchive->open($zipFileNameTemp, \ZipArchive::CREATE) !== true) {
            throw new Exception('Could not create zip archive.');
        }

        try {
            foreach ($files as $file) {
                $fileName = $this->getAbsolutePath($file);

                $zipArchive->addFile($fileName, $file);
            }
        } finally {
            $zipArchive->close();
            $zipArchiveBinary = \file_get_contents($zipFileNameTemp);
            \unlink($zipFileNameTemp);
        }

        if ($zipArchiveBinary === false) {
            throw new Exception('Could not read from temporary zip file.');
        }

        $this->fileSystem->write($outputDir, $zipArchiveBinary);

        return $outputDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompressedLogArchive(string $path): string
    {
        $content = null;
        if ($this->fileSystem->has($path)) {
            $content = $this->fileSystem->read($path);
        }

        if (!$content) {
            throw new FileNotFoundException($path);
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanupLogArchives(): void
    {
        $publicDirectoryContent = $this->fileSystem->listContents(self::DIRECTORY_NAME);

        $yesterdayTimestamp = \strtotime('-1 day');

        foreach ($publicDirectoryContent as $entry) {
            $fileExtension = \pathinfo($entry['path'], \PATHINFO_EXTENSION);
            $fileTimestamp = $entry['timestamp'] ?? $entry['lastModified'];

            // Only .zip files and only if older than 1 day
            if ($fileExtension !== 'zip' || $fileTimestamp > $yesterdayTimestamp) {
                continue;
            }

            $this->fileSystem->delete($entry['path']);
        }
    }
}
