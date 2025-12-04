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
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

MLFilesystem::gi()->loadClass('Database_Model_Table_Prepare_Abstract');

class ML_Amazon_Model_Table_Amazon_PrepareLongText extends ML_Database_Model_Table_Prepare_Abstract {

    protected $sTableName = 'magnalister_amazon_prepare_longtext';

    protected $aFields = array(
        'TextId'                     => array(
            'isKey' => true,
            'Type' => 'varchar(64)', 'Null' => self::IS_NULLABLE_NO, 'Default' => NULL, 'Extra' => '', 'Comment' => 'sha coding from text'
        ),
        'ReferenceFieldName'               => array(
            'isKey' => true,
            'Type' => 'varchar(64)', 'Null' => self::IS_NULLABLE_NO, 'Default' => NULL, 'Extra' => '', 'Comment' => 'e.g "ShopVariation",""'
        ),
        'Value'               => array(
            'Type' => 'longtext', 'Null' => self::IS_NULLABLE_YES, 'Default' => NULL, 'Extra' => '', 'Comment' => 'JSON encoded attribute data'
        ),
        'CreatedAt'           => array(
            'Type'    => 'datetime',
            'Null'    => self::IS_NULLABLE_YES,
            'Default' => 'CURRENT_TIMESTAMP',
            'Extra'   => '',
            'Comment' => 'datetime of creation'
        )
    );

    protected $aTableKeys = array(
        'UC_TextIdReferenceFieldName'               => array('Non_unique' => '0', 'Column_name' => 'TextId, ReferenceFieldName'),
    );

    protected function setDefaultValues() {
        // TODO: Implement setDefaultValues() method.
    }

    public function getValue($sTextId, $sNameLower) {
        static $aCache = array();
        if (!isset($aCache[$sTextId][$sNameLower])) {
            $sSql = "
                        SELECT Value
                        FROM `" . $this->sTableName . "`
                        WHERE TextId = '" . MLDatabase::getDbInstance()->escape($sTextId) . "'
                          AND ReferenceFieldName = '" . MLDatabase::getDbInstance()->escape($sNameLower) . "'
                        LIMIT 1
                    ";
            $value = MLDatabase::getDbInstance()->fetchOne($sSql);
            if (!empty($value)) {
                $aCache[$sTextId][$sNameLower] = $value;
            }
        }
        return $aCache[$sTextId][$sNameLower];
    }
}
