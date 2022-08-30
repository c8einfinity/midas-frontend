<?php
/**
 * Quotes\Collection.php
 */
namespace Motus\Quotesystem\Model\ResourceModel\QuoteDetails;

use \Motus\Quotesystem\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
     /**
      * @var string
      */
    protected $_idFieldName = 'entity_id';
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Motus\Quotesystem\Model\QuoteDetails ::class,
            \Motus\Quotesystem\Model\ResourceModel\QuoteDetails ::class
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
    public function setTableRecords($ids, $columnData)
    {
        return $this->getConnection()->update(
            $this->getTable('mot_quote_details'),
            $columnData,
            $where = $ids
        );
    }
     /**
      * Retrieve all mageproduct_id for collection
      *
      * @param  int|string $limit
      * @param  int|string $offset
      * @return array
      */
    public function getAllIds($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('entity_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }
    /**
     * Retrieve clear select
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function _getClearSelect()
    {
        return $this->_buildClearSelect();
    }
     /**
      * Build clear select
      *
      * @param  \Magento\Framework\DB\Select $select
      * @return \Magento\Framework\DB\Select
      */
    protected function _buildClearSelect($select = null)
    {
        if (null === $select) {
            $select = clone $this->getSelect();
        }
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);

        return $select;
    }
}
