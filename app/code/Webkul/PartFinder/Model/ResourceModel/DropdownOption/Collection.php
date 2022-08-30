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
namespace Webkul\PartFinder\Model\ResourceModel\DropdownOption;

use Magento\Framework\EntityManager\MetadataPool;
use Webkul\PartFinder\Api\Data\PartfinderDropdownInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        MetadataPool $metadataPool = null
    ) {
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
            \Webkul\PartFinder\Model\DropdownOption::class,
            \Webkul\PartFinder\Model\ResourceModel\DropdownOption::class
        );
    }

    /**
     * Add option to filter
     *
     * @param array|\Webkul\PartFinder\Model\Partfinder\PartfinderDropdown|int $dropdown
     * @return $this
     */
    public function addDropdownToFilter($dropdown)
    {
        if (empty($dropdown)) {
            $this->addFieldToFilter('dropdown_id', '');
        } elseif (is_array($dropdown)) {
            $this->addFieldToFilter('dropdown_id', ['in' => $dropdown]);
        } elseif ($option instanceof \Webkul\PartFinder\Model\Partfinder\PartfinderDropdown) {
            $this->addFieldToFilter('dropdown_id', $dropdown->getId());
        } else {
            $this->addFieldToFilter('dropdown_id', $dropdown);
        }

        return $this;
    }

    public function addFilterToData($val, $var)
    {
        return $this->addFieldToFilter($val, $var);
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
            ['pfd' => $this->getTable('partfinder_dropdown')],
            sprintf(
                'pfd.%s = main_table.dropdown_id',
                $this->metadataPool->getMetadata(PartfinderDropdownInterface::class)->getLinkField()
            ),
            []
        );
    }

    /**
     * @param int $dropdownId
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface[]
     */
    public function getDropdownOptions($dropdownId)
    {
        $collection = $this->addFieldToFilter(
            'pfd.entity_id',
            $dropdownId
        )->setOrder(
            'sort_order',
            'asc'
        )->setOrder(
            'label',
            'asc'
        );
        return $collection->getItems();
    }
}
