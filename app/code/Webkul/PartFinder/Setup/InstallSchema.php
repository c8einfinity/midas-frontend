<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_PartFinder
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\PartFinder\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        //Your install script

        $setup->startSetup();

        $partFinder = $setup->getConnection()->newTable($setup->getTable('partfinder_index'));

        $partFinder->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
            'Entity ID'
        );

        $partFinder->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        );
        $partFinder->addColumn(
            'widget_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'widget name'
        );

        $partFinder->addColumn(
            'widget_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'widget code'
        );

        $partFinder->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            [],
            'status'
        );

        $partFinder->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'created_at'
        );

        $partFinder->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'store_id'
        );
        $partFinder->addColumn(
            'dropdown_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [],
            'dropdown_count'
        );

        $setup->getConnection()->createTable($partFinder);

        $partFinderdropdown = $setup->getConnection()->newTable($setup->getTable('partfinder_dropdown'));

        $partFinderdropdown->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
            'Entity ID'
        );

        $partFinderdropdown->addColumn(
            'finder_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'finder_id'
        );

        $partFinderdropdown->addColumn(
            'label',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'label'
        );

        $partFinderdropdown->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'attribute_id'
        );

        $partFinderdropdown->addColumn(
            'is_mapped',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            [],
            'is_mapped'
        );

        $partFinderdropdown->addColumn(
            'is_required',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            [],
            'is_required'
        );

        $partFinderdropdown->addColumn(
            'option_sorting',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'option sorting'
        );

        $partFinderdropdown->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'sort order'
        );

        $partFinderdropdown->addForeignKey(
            $setup->getFkName(
                'partfinder_dropdown',
                'finder_id',
                'partfinder_index',
                'entity_id'
            ),
            'finder_id',
            $setup->getTable('partfinder_index'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $partFindercategory = $setup->getConnection()->newTable($setup->getTable('partfinder_category'));

        $partFindercategory->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
            'Entity ID'
        );

        $partFindercategory->addColumn(
            'finder_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'finder_id'
        );

        $partFindercategory->addColumn(
            'category_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'category_id'
        );

        $partFindercategory->addForeignKey(
            $setup->getFkName(
                'partfinder_category',
                'finder_id',
                'partfinder_index',
                'entity_id'
            ),
            'finder_id',
            $setup->getTable('partfinder_index'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $partFinderproduct = $setup->getConnection()->newTable($setup->getTable('partfinder_product'));

        $partFinderproduct->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true],
            'Entity ID'
        );

        $partFinderproduct->addColumn(
            'finder_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => false,'nullable' => false,'primary' => false,'unsigned' => true],
            'finder_id'
        );

        $partFinderproduct->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'product_id'
        );

        $partFinderproduct->addForeignKey(
            $setup->getFkName(
                'partfinder_product',
                'finder_id',
                'partfinder_index',
                'entity_id'
            ),
            'finder_id',
            $setup->getTable('partfinder_index'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $dropdownOption = $setup->getConnection()->newTable($setup->getTable('partfinder_dropdown_option'));

        $dropdownOption->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
            'Entity ID'
        );

        $dropdownOption->addColumn(
            'dropdown_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'dropdown_id'
        );

        $dropdownOption->addColumn(
            'label',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'label'
        );

        $dropdownOption->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'value'
        );

        $dropdownOption->addForeignKey(
            $setup->getFkName(
                'partfinder_dropdown_option',
                'dropdown_id',
                'partfinder_dropdown',
                'entity_id'
            ),
            'dropdown_id',
            $setup->getTable('partfinder_dropdown'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($partFinderproduct);
        $setup->getConnection()->createTable($partFindercategory);
        $setup->getConnection()->createTable($partFinderdropdown);
        $setup->getConnection()->createTable($dropdownOption);

        $table = $setup->getConnection()->newTable($setup->getTable('partfinder_product_selection'));

        $table->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
            'Entity ID'
        );

        $table->addColumn(
            'finder_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => false,'nullable' => false,'primary' => false,'unsigned' => true],
            'finder_id'
        );

        $table->addColumn(
            'dropdown_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'dropdown_id'
        );

        $table->addColumn(
            'option_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'option_id'
        );

        $table->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'product_id'
        );
        $table->addColumn(
            'variation_key',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'variation key'
        );
        $table->addForeignKey(
            $setup->getFkName(
                'partfinder_product_selection',
                'finder_id',
                'partfinder_index',
                'entity_id'
            ),
            'finder_id',
            $setup->getTable('partfinder_index'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable($setup->getTable('partfinder_profiledata'));

        $table->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
            'Entity ID'
        );

        $table->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'profile name'
        );

        $table->addColumn(
            'mapping',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'mapping'
        );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable($setup->getTable('partfinder_website'));

        $table->addColumn(
            'finder_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => false,'nullable' => false,'primary' => false,'unsigned' => true],
            'finder_id'
        );

        $table->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => false,'nullable' => false,'primary' => false,'unsigned' => true],
            'website_id'
        );
        $table->addForeignKey(
            $setup->getFkName(
                'partfinder_website',
                'finder_id',
                'partfinder_index',
                'entity_id'
            ),
            'finder_id',
            $setup->getTable('partfinder_index'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $table->addForeignKey(
            $setup->getFkName(
                'partfinder_website',
                'website_id',
                'store_website',
                'website_id'
            ),
            'website_id',
            $setup->getTable('store_website'),
            'website_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
