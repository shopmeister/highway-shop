<?php declare(strict_types=1);

namespace Redgecko\Magnalister\Resources\snippet\en_US;

use Shopware\Core\System\Snippet\Files\SnippetFileInterface;

class SnippetFile_en_US implements SnippetFileInterface
{
    public function getName(): string
    {
        return 'storefront.en-US';
    }

    public function getPath(): string
    {
        return __DIR__ . '/storefront.en-US.json';
    }

    public function getIso(): string
    {
        return 'en-US';
    }

    public function getAuthor(): string
    {
        return 'Enter developer name here';
    }

    public function isBase(): bool
    {
        return false;
    }
}
