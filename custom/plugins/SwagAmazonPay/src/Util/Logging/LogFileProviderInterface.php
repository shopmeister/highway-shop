<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\AmazonPay\Util\Logging;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

interface LogFileProviderInterface
{
    /**
     * Returns an array of file names inside the selected log directory.
     */
    public function getLogFileList(): array;

    /**
     * Returns the absolute path to a logfile by the provided file name.
     */
    public function getAbsolutePath(string $fileName): string;

    /**
     * Creates a new zip archive of the given log files and moves it to the
     * public folder where it can be used as download.
     *
     * Returns the relative path to the archive which has been created.
     */
    public function compressLogFiles(array $files): string;

    /**
     * Returns the file contents of a generated log archive.
     *
     * @throws FileNotFoundException
     */
    public function getCompressedLogArchive(string $path): string;

    /**
     * Cleans up the public resources folder
     */
    public function cleanupLogArchives(): void;
}
