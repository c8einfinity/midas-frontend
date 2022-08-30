<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Motus\Quotesystem\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\AggregatedFieldDataConverter;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class InsertDataPatch implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        AggregatedFieldDataConverter $aggregatedFieldConverter
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->aggregatedFieldConverter = $aggregatedFieldConverter;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'quote_status',
            [
                'label' => 'Quote Status',
                'type' => 'int',
                'input' => 'select',
                'group' => 'Product Details',
                'source' => \Motus\Quotesystem\Model\Product\Attribute\Options ::class,
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'visible_on_front' => false,
                'is_configurable' => false,
                'searchable' => true,
                'default' => '1',
                'filterable' => true,
                'comparable' => true,
                'visible_in_advanced_search' => true,
                'note' => 'Do you wish to enable Quote on this product',
                'apply_to' => 'simple,downloadable,virtual,bundle,configurable',
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'min_quote_qty',
            [
                'label' => 'Minimum Quote Quantity',
                'input' => 'text',
                'group' => 'Product Details',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean ::class,
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend ::class,
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'visible_on_front' => false,
                'is_configurable' => false,
                'searchable' => true,
                'default' => '',
                'filterable' => true,
                'comparable' => true,
                'visible_in_advanced_search' => false,
                'note' => 'Minimum Quote quantity for this product',
                'apply_to' => 'simple,downloadable,virtual,bundle,configurable',
            ]
        );

        $data = [];
        // convert serialized data to json format
        $fieldsToUpdate[] = new FieldToConvert(
            SerializedToJson::class,
            $this->moduleDataSetup->getTable('mot_quotes'),
            'entity_id',
            'product_option'
        );
        $fieldsToUpdate[] = new FieldToConvert(
            SerializedToJson::class,
            $this->moduleDataSetup->getTable('mot_quotes'),
            'entity_id',
            'links'
        );
        $fieldsToUpdate[] = new FieldToConvert(
            SerializedToJson::class,
            $this->moduleDataSetup->getTable('mot_quotes'),
            'entity_id',
            'bundle_option'
        );
        $fieldsToUpdate[] = new FieldToConvert(
            SerializedToJson::class,
            $this->moduleDataSetup->getTable('mot_quotes'),
            'entity_id',
            'super_attribute'
        );
        $this->aggregatedFieldConverter->convert($fieldsToUpdate, $this->moduleDataSetup->getConnection());
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
