<?php

namespace ShopmasterZalandoConnectorSix\Bootstrap\CustomField;


use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Order\OrderCustomFields;
use ShopmasterZalandoConnectorSix\Bootstrap\CustomField\Product\ProductCustomFields;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CustomFieldManager
{

    const SET_LIST = [
        ProductCustomFields::class,
        OrderCustomFields::class,
    ];


    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;
    /**
     * @var EntityRepository
     */
    private EntityRepository $customFieldSetRepository;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->customFieldSetRepository = $this->container->get('custom_field_set.repository');
    }

    /**
     * @return void
     */
    public function makeCustomFieldSets()
    {
        $this->deleteCustomFieldSets();
        $this->customFieldSetRepository->upsert($this->getCustomFieldSets(), Context::createDefaultContext());
    }

    /**
     * @return void
     */
    public function deleteCustomFieldSets()
    {
        $this->customFieldSetRepository->delete($this->getCustomFieldSetsId(), Context::createDefaultContext());
    }

    /**
     * @return array
     */
    private function getCustomFieldSets(): array
    {
        $data = [];
        /** @var CustomFieldsInterface $setClass */
        foreach (self::SET_LIST as $setClass) {
            $data[] = $setClass::getSet();
        }
        return $data;
    }

    /**
     * @return array
     */
    private function getCustomFieldSetsId(): array
    {
        $data = [];
        /** @var CustomFieldsInterface $setClass */
        foreach (self::SET_LIST as $setClass) {
            $data[] = ['id' => $setClass::getSetId()];
        }
        return $data;
    }

}