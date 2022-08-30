<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_PartFinder
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\PartFinder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\PartFinder\Model\ResourceModel\ProductSelection\CollectionFactory as ProductSelectionCollectionFactory;

class ProcessProductAfterDeleteEventObserver implements ObserverInterface
{
    
    public function __construct(
        ProductSelectionCollectionFactory $productSelectionCollectionFactory
    ) {
        $this->productSelectionCollectionFactory = $productSelectionCollectionFactory;
    }

    /**
     *
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $eventProduct = $observer->getEvent()->getProduct();
        if ($eventProduct && $eventProduct->getId()) {
            $collection = $this->productSelectionCollectionFactory->create();
            $productSelection = $collection->addFieldToFilter('product_id', ['eq' => $eventProduct->getId()]);
            
            if ($productSelection->getSize() > 0) {
                foreach ($productSelection as $item) {
                    $item->delete();
                }
            }
        }
        return $this;
    }
}
