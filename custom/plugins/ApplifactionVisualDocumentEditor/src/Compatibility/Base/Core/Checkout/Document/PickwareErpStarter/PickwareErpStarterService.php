<?php
/*
 * Copyright (c) Applifaction LLC. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

namespace Applifaction\DragNDropDocumentEditor\Compatibility\Base\Core\Checkout\Document\PickwareErpStarter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 *
 */
class PickwareErpStarterService
{

    const PICKWARE_ERP_INVOICE_CORRECTION = 'pickware_erp_invoice_correction';

    private Connection $connection;

    public function __construct(
        Connection                      $connection
    )
    {
        $this->connection = $connection;
    }

    /**
     * @return false|string
     * @throws Exception
     */
    public function fetchOrderNumberWithInvoiceCorrection(): false|string
    {
        $sql = <<<SQL
            select o.order_number
            from document d
                     inner join document_type dt on d.document_type_id = dt.id
                     inner join `order` o on d.order_id = o.id and d.order_version_id = o.version_id
                     left join (select sd.id                     as document_id,
                                       sd.referenced_document_id as reference_document_id
                                from document sd
                                         inner join document_type sdt on sd.document_type_id = sdt.id
                                where sdt.technical_name = 'storno') storno on storno.reference_document_id = d.id
                     left join (select icd.id                                                               as document_id,
                                       JSON_EXTRACT(icd.config, '$.custom.pickwareErpReferencedDocumentId') as invoice_correction_document_id
                                from document icd
                                         inner join document_type icdt on icd.document_type_id = icdt.id
                                where icdt.technical_name = 'pickware_erp_invoice_correction') invoice_correction
                               on invoice_correction.invoice_correction_document_id = LOWER(HEX(d.id))
                     left join pickware_erp_return_order pero on o.id = pero.order_id and pero.order_version_id = :live_version_id
                     left join state_machine_state sms on pero.state_id = sms.id
            where dt.technical_name = 'invoice'
              and invoice_correction.invoice_correction_document_id is null
              and storno.document_id is null
              and sms.technical_name = 'received'
            order by o.created_at DESC
            LIMIT 1;
SQL;

        return $this->connection->fetchOne($sql, ['live_version_id' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION)]);
    }

}
