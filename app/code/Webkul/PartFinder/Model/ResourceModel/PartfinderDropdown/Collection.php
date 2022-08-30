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
namespace Webkul\PartFinder\Model\ResourceModel\PartfinderDropdown;

use Magento\Framework\EntityManager\MetadataPool;
use Webkul\PartFinder\Api\Data\PartfinderInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    
    private $optionsCollectionFactory;

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
        \Webkul\PartFinder\Model\ResourceModel\DropdownOption\CollectionFactory $optionsCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        MetadataPool $metadataPool = null
    ) {
        $this->optionsCollectionFactory = $optionsCollectionFactory;
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
            \Webkul\PartFinder\Model\Partfinder\PartfinderDropdown::class,
            \Webkul\PartFinder\Model\ResourceModel\PartfinderDropdown::class
        );
    }

    /**
     * Add finder_id filter to select
     *
     * @param array|\Webkul\PartFinder\Model\Partfinder|int $finder
     * @return $this
     */
    public function addFinderToFilter($finder)
    {
        if (empty($finder)) {
            $this->addFieldToFilter('finder_id', '');
        } elseif (is_array($finder)) {
            $this->addFieldToFilter('finder_id', ['in' => $finder]);
        } elseif ($product instanceof \Webkul\PartFinder\Model\Partfinder) {
            $this->addFieldToFilter('finder_id', $finder->getId());
        } else {
            $this->addFieldToFilter('finder_id', $finder);
        }

        return $this;
    }

    /**
     * Add value to result
     *
     * @param int $storeId
     * @return $this
     */
    public function addValuesToResult($storeId = null)
    {
        $dropdownIds = [];
        foreach ($this as $dropdown) {
            $dropdownIds[] = $dropdown->getId();
        }
        
        if (!empty($dropdownIds)) {
            /** @var \Webkul\PartFinder\Model\ResourceModel\DropdownOption\Collection $values */
            $values = $this->optionsCollectionFactory->create();
            $values->addDropdownToFilter(
                $dropdownIds
            )->setOrder(
                'label',
                self::SORT_ORDER_ASC
            );
            foreach ($values as $value) {
                $optionId = $value->getDropdownId();
                if ($this->getItemById($optionId)) {
                    $this->getItemById($optionId)->addOptions($value);
                }
            }
        }
        return $this;
    }

    /**
     * @return void
     * @throws \Exception
     * @since 101.0.0
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->join(
            ['pfi' => $this->getTable('partfinder_index')],
            sprintf(
                'pfi.%s = main_table.finder_id',
                $this->metadataPool->getMetadata(PartfinderInterface::class)->getLinkField()
            ),
            []
        );
    }

    /**
     * Get Finder Dropdowns
     *
     * @param int $productId
     * @param int $storeId
     * @param bool $requiredOnly
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface[]
     */
    public function getFinderDropdowns($finderId, $storeId, $requiredOnly = false)
    {
        $collection = $this->addFieldToFilter(
            'pfi.entity_id',
            $finderId
        )->setOrder(
            'sort_order',
            'asc'
        )->setOrder(
            'label',
            'asc'
        );
        if ($requiredOnly) {
            $collection->addRequiredFilter();
        }
        $collection->addValuesToResult($storeId);
        return $collection->getItems();
    }

    /**
     * Add is_required filter to select
     *
     * @param bool $required
     * @return $this
     */
    public function addRequiredFilter($required = true)
    {
        $this->addFieldToFilter('main_table.is_require', (int)$required);
        return $this;
    }
}
