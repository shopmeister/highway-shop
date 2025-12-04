<?php

namespace ShopmasterZalandoConnectorSix\Bootstrap\CustomField;

interface CustomFieldsInterface
{
    const PRODUCT_ADDITIONAL_SET_NAME = 'sm_zalandoConnector_product';
    const ORDER_ADDITIONAL_SET_NAME = 'sm_zalandoConnector_order';


    public static function getSetId(): string;

    public static function getSet(): array;


}