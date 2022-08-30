<?php
/**
 * Quote\Grid\Collection.php
 */

namespace Motus\Quotesystem\Model\ResourceModel\Quotes\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Motus\Quotesystem\Model\ResourceModel\Quotes\Collection as QuoteCollection;

class Collection extends QuoteCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $_aggregations;

    protected $eavAttribute;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory
     * @param \Psr\Log\LoggerInterface                                     $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param [type]                                                       $mainTable
     * @param [type]                                                       $eventPrefix
     * @param [type]                                                       $eventObject
     * @param [type]                                                       $resourceModel
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute            $eavAttribute
     * @param string                                                       $model
     * @param [type]                                                       $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb         $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document ::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->eavAttribute = $eavAttribute;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->_aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->_aggregations = $aggregations;
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
     
    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection
     *
     * @param  int $limit
     * @param  int $offset
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
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param  \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param  int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param   \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return  $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    protected function _initSelect()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $backendSession = $objectManager->create(\Magento\Backend\Model\Session::class);
        $quoteId = $backendSession->getQuoteId();
        $eavAttribute = $this->eavAttribute;
        $proAttrId = $eavAttribute->getIdByCode("catalog_product", "name");
        $catalogProductEntityVarchar = $this->getTable('catalog_product_entity_varchar');
        $this->getSelect()->join(
            $catalogProductEntityVarchar.' as cpev',
            'main_table.product_id = cpev.entity_id',
            [
                'name'=>'value',
            ]
        )->where("cpev.attribute_id = ".$proAttrId);
        $joinTable = $this->getTable('customer_grid_flat');
        $this->getSelect()->join(
            $joinTable.' as cgf',
            'main_table.customer_id = cgf.entity_id',
            [
                'customer_name'=>'name',
                'email'=>'email',
            ]
        );
        $this->addFilterToMap("name", "cpev.value");
        $this->addFilterToMap("customer_name", "cgf.name");
        $this->addFilterToMap("email", "cgf.email");
        $this->addFieldToFilter('quote_id', $quoteId);
        parent::_initSelect();
    }

    /**
     * Join to get Product name in grid.
     */
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->group("main_table.entity_id");
        parent::_renderFiltersBefore();
    }
}
