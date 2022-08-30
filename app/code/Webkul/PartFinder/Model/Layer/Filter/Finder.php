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
namespace Webkul\PartFinder\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Webkul\PartFinder\Model\PartfinderFactory;
use Webkul\PartFinder\Model\ResourceModel\ProductSelection\CollectionFactory;

class Finder extends AbstractFilter
{
    /**
     * @var boolean $_activeFilter
     */
    protected $_activeFilter = false;

    /**
     * @var string $_requestVar
     */
    protected $_requestVar;

    /**
     * @var \Webkul\PartFinder\Model\PartfinderFactory
     */
    protected $finderFactory;

    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\ProductSelection\CollectionFactory
     */
    protected $selectionCollectionFactory;

    /**
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param DataBuilder $itemDataBuilder
     * @param PartfinderFactory $finderFactory
     * @param CollectionFactory $selectionCollectionFactory
     * @param array $data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        PartfinderFactory $finderFactory,
        CollectionFactory $selectionCollectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->_requestVar = 'finder';
        $this->finderFactory = $finderFactory;
        $this->selectionCollectionFactory = $selectionCollectionFactory;
    }
    
    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $filter = $request->getParam($this->getRequestVar(), null);
        
        if ($filter === null) {
            return $this;
        }
        $filters = explode('_', $filter);
        
        $selectionCollection = $this->selectionCollectionFactory->create();
        $conditions = [];
        foreach ($filters as $filter) {
            $conditions[] = [
                'like' => "$filter%"
            ];
        }
        $selectionCollection->addFieldToFilter(
            ['variation_key'],
            [$conditions]
        );

        $finderIds = array_unique($selectionCollection->getAllFinderIds());
        $finders = [];
        foreach ($finderIds as $finderId) {
            $finderModel = $this->finderFactory->create();
            $finder = $this->loadObject($finderModel, $finderId);
            if ($finder->getId()) {
                $finders[] = $finder->getId();
            }
        }
        $selectionCollection->addFieldToFilter(
            'finder_id',
            ['in' => $finders]
        );
       
        $collection = $this->getLayer()->getProductCollection();
        if ($selectionCollection->getSize()) {
            $collection->getSelect()
            ->where("e.entity_id IN(".implode(',', $selectionCollection->getAllProductIds()).")");
        } else {
            $collection->getSelect()
            ->where("e.entity_id IN(0)");
        }
        return $this;
    }

    /**
     * Get filter name
     *
     * @return string
     */
    public function getName()
    {
        return __('Finder Result');
    }

    /**
     * Get data array for building status filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $data = [];
        return $data;
    }

    /**
     * Get Products Count
     *
     * @param $value
     * @return string
     */
    public function getProductsCount($value)
    {
        $collection = $this->getLayer()->getProductCollection();
        $select = clone $collection->getSelect();
        // reset columns, order and limitation conditions
        $select->reset(\Zend_Db_Select::COLUMNS);
        $select->reset(\Zend_Db_Select::ORDER);
        $select->reset(\Zend_Db_Select::LIMIT_COUNT);
        $select->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $select->where('stock_status_index.stock_status = ?', $value);
        $select->columns(
            [
                'count' => new \Zend_Db_Expr("COUNT(e.entity_id)")
            ]
        );
        return $collection->getConnection()->fetchOne($select);
    }

    /**
     * Load Object by ID
     *
     * @param object $model
     * @param int $id
     * @return object $object
     */
    protected function loadObject($model, $id)
    {
        $object = $model->load($id);
        return $object;
    }
}
