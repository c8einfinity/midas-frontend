<?php
/**
 * Quote conversation Collection.php
 */

namespace Motus\Quotesystem\Model\ResourceModel\Quoteconversation;

use \Motus\Quotesystem\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Motus\Quotesystem\Model\Quoteconversation ::class,
            \Motus\Quotesystem\Model\ResourceModel\Quoteconversation ::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
}
