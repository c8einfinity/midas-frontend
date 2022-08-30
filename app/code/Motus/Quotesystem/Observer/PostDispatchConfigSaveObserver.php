<?php
/**
 Do you wish to enable Quote on this product.
 */

namespace Motus\Quotesystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class PostDispatchConfigSaveObserver implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $eavSetup;

    protected $setup;

    /**
     * @param ManagerInterface                           $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param EavSetup                                   $eavSetup
     * @param ModuleDataSetupInterface                   $setup
     */
    public function __construct(
        ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        EavSetup $eavSetup,
        ModuleDataSetupInterface $setup
    ) {
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->eavSetup = $eavSetup;
        $this->setup = $setup;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $observerRequestData = $observer['request'];
            $params = $observerRequestData->getParams();
            if ($params['section'] == 'quotesystem' && isset(
                $params['groups']['mot_quotesystemsetting']['fields']['mot_quotesystemenabledisable']
            )) {
                $moduleStatus = $params['groups']['mot_quotesystemsetting']
                                            ['fields']['mot_quotesystemenabledisable']['value'];
                if ($moduleStatus) {
                    $this->eavSetup->addAttributeToSet(
                        \Magento\Catalog\Model\Product::ENTITY,
                        'Default',
                        'General',
                        'quote_status'
                    );
                    $this->eavSetup->addAttributeToSet(
                        \Magento\Catalog\Model\Product::ENTITY,
                        'Default',
                        'General',
                        'min_quote_qty'
                    );
                } else {
                    $this->setup->deleteTableRow(
                        'eav_entity_attribute',
                        'attribute_id',
                        $this->eavSetup->getAttributeId('catalog_product', 'quote_status'),
                        'attribute_set_id',
                        $this->eavSetup->getAttributeSetId('catalog_product', 'Default')
                    );
                    $this->setup->deleteTableRow(
                        'eav_entity_attribute',
                        'attribute_id',
                        $this->eavSetup->getAttributeId('catalog_product', 'min_quote_qty'),
                        'attribute_set_id',
                        $this->eavSetup->getAttributeSetId('catalog_product', 'Default')
                    );
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}
