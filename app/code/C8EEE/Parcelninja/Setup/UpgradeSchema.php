<?php

namespace C8EEE\Parcelninja\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $quote = 'quote';
        $orderTable = 'sales_order';
        $setupConnection = $setup->getConnection();

        $setupConnection
            ->addColumn(
                $setup->getTable($quote),
                'pn_quote_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' =>'Parcel Ninja quote Id'
                ]
            );
        $setupConnection
            ->addColumn(
                $setup->getTable($quote),
                'pn_cost',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'comment' =>'Parcel Ninja cost'
                ]
            );
        //Order table
        $setupConnection
            ->addColumn(
                $setup->getTable($orderTable),
                'pn_quote_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'comment' =>'Parcel Ninja quote Id'
                ]
            );
        $setupConnection
            ->addColumn(
                $setup->getTable($orderTable),
                'pn_cost',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'comment' =>'Parcel Ninja cost'
                ]
            );

        $setup->endSetup();
    }
}
