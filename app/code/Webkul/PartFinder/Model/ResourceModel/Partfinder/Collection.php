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
namespace Webkul\PartFinder\Model\ResourceModel\Partfinder;

use Magento\Framework\EntityManager\MetadataPool;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Alias for main table
     */
    const MAIN_TABLE_ALIAS = 'e';

    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    
    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\PartfinderDropdown\CollectionFactory
     */
    protected $dropdownCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Need to add websites to result flag
     *
     * @var bool
     */
    protected $needToAddWebsiteNamesToResult;

    /**
     * Need to add dropdowns to result flag
     *
     * @var bool
     */
    protected $needToAddDropdownsToResult;
    
    /**
     * dropdown table name
     *
     * @var string
     */
    protected $_finderDropdownsTable;

    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\Partfinder\Collection\FinderLimitation
     */
    protected $_finderLimitationFilters;
    
    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory
     * @param \Psr\Log\LoggerInterface                                     $loggerInterface
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyInterface
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManagerInterface
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null          $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null    $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Webkul\PartFinder\Model\ResourceModel\PartfinderDropdown\CollectionFactory $dropdownCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        MetadataPool $metadataPool = null
    ) {
        $this->dropdownCollectionFactory = $dropdownCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->metadataPool = $metadataPool ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\PartFinder\Model\Partfinder::class,
            \Webkul\PartFinder\Model\ResourceModel\Partfinder::class
        );
        $this->_initTables();
    }

     /**
      * Define finder website and category finder tables
      *
      * @return void
      */
    protected function _initTables()
    {
        $this->_finderWebsiteTable = $this->getResource()->getTable('partfinder_website');
        $this->_finderDropdownsTable = $this->getResource()->getTable('partfinder_dropdown');
        $this->_finderCategoryTable = $this->getResource()->getTable('partfinder_category');
    }

    /**
     * Retrieve array of attributes
     *
     * @param array $arrAttributes
     * @return array
     */
    public function toArray($arrAttributes = [])
    {
        $arr = [];
        foreach ($this->getItems() as $key => $item) {
            $arr[$key] = $item->toArray($arrAttributes);
        }
        return $arr;
    }

    /**
     * Apply limitation filters to collection
     *
     * @return $this
     */
    protected function _applyProductLimitations()
    {
        $this->_prepareFinderLimitationFilters();
        $this->_finderDropdownCountJoinWebsite();
    }

    /**
     * Prepare limitation filters
     *
     * @return $this
     */
    protected function _prepareFinderLimitationFilters()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        parent::load($printQuery, $logQuery);
        if ($this->needToAddWebsiteNamesToResult) {
            $this->doAddWebsiteNamesToResult();
        }

        if ($this->needToAddDropdownsToResult) {
            $this->doAddDropdownCountToResult();
        }
        return $this;
    }

    /**
     * Adding finder website names to result collection
     * Add for each finder websites information
     *
     * @return $this
     */
    public function addWebsiteNamesToResult()
    {
        $this->needToAddWebsiteNamesToResult = true;
        return $this;
    }

    /**
     * Processs adding finder website names to result collection
     *
     * @return $this
     */
    protected function doAddWebsiteNamesToResult()
    {
        $finderWebsites = [];
        foreach ($this as $finder) {
            $finderWebsites[$finder->getId()] = [];
        }
        
        if (!empty($finderWebsites)) {
            $select = $this->getConnection()->select()->from(
                ['finder_website' => $this->_finderWebsiteTable]
            )->join(
                ['website' => $this->getResource()->getTable('store_website')],
                'website.website_id = finder_website.website_id',
                ['name']
            )->where(
                'finder_website.finder_id IN (?)',
                array_keys($finderWebsites)
            )->where(
                'website.website_id > ?',
                0
            );

            $data = $this->getConnection()->fetchAll($select);
            
            foreach ($data as $row) {
                $finderWebsites[$row['finder_id']][] = $row['website_id'];
            }
        }
        foreach ($this as $finder) {
            if (isset($finderWebsites[$finder->getId()])) {
                $finder->setData('websites', $finderWebsites[$finder->getId()]);
            }
        }
        return $this;
    }
    
    /**
     * Processing collection items after loading
     * Adding dropdowns to collection
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $findersById = [];
        foreach ($this as $finder) {
            $finderId = $finder->getData('entity_id');
            $findersById[$finderId] = $finder;
        }

        if (!empty($findersById)) {
            $dropdowns = $this->dropdownCollectionFactory->create()
            ->addFinderToFilter(
                array_keys($findersById)
            )->setOrder(
                'sort_order',
                self::SORT_ORDER_ASC
            )->addValuesToResult();
            
            foreach ($dropdowns as $dropdown) {
                if (isset($findersById[$dropdown->getFinderId()])) {
                    $findersById[$dropdown->getFinderId()]->addDropdowns($dropdown);
                }
            }
        }
        return $this;
    }
}
