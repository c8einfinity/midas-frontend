<?php
/**
 * Motus
 */
namespace Motus\Quotesystem\Plugin\CatalogSearch\Model\Layer\Category;

class ItemCollectionProvider
{
    public function afterGetCollection(
        \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider $subject,
        $result
    ) {
        $collection = $result->addAttributeToSelect('quote_status');
        return $collection;
    }
}
