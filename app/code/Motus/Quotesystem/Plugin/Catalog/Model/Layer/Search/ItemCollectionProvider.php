<?php
/**
 * Motus
 */

namespace Motus\Quotesystem\Plugin\Catalog\Model\Layer\Search;

class ItemCollectionProvider
{

    public function afterGetCollection(
        \Magento\Catalog\Model\Layer\Search\ItemCollectionProvider $subject,
        $result
    ) {
        return $result->addAttributeToSelect('quote_status');
    }
}
