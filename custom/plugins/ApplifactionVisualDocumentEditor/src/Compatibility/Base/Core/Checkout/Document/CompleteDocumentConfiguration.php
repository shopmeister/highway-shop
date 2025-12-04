<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

declare(strict_types=1);

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document;

use Shopware\Core\Checkout\Document\DocumentConfiguration;

/**
 * Fix for @see https://github.com/shopware/platform/issues/2672
 */
class CompleteDocumentConfiguration extends DocumentConfiguration
{
    /**
     * @var string|null
     */
    protected $companyPhone;

    /**
     * @var array|null
     */
    protected $dompdfOptions;

    public function getCompanyPhone(): ?string
    {
        return $this->companyPhone;
    }

    public function setCompanyPhone(?string $companyPhone): void
    {
        $this->companyPhone = $companyPhone;
    }

    public function getDompdfOptions(): array
    {
        return $this->dompdfOptions ?? [];
    }

    public function setDompdfOptions(?array $dompdfOptions): void
    {
        $this->dompdfOptions = $dompdfOptions;
    }

}
