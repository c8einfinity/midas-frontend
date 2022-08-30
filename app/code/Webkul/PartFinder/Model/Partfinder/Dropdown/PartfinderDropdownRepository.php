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
namespace Webkul\PartFinder\Model\Partfinder\Dropdown;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotDeleteException;
use Webkul\PartFinder\Model\ResourceModel\PartfinderDropdown as ResourcePartfinderDropdown;
use Webkul\PartFinder\Model\Partfinder\PartfinderDropdownFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\PartFinder\Api\PartfinderDropdownRepositoryInterface;
use Webkul\PartFinder\Api\Data\PartfinderDropdownInterfaceFactory;
use Webkul\PartFinder\Api\Data\PartfinderDropdownSearchResultsInterfaceFactory;
use Webkul\PartFinder\Model\ResourceModel\PartfinderDropdown\CollectionFactory as collectionFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;

class PartfinderDropdownRepository implements PartfinderDropdownRepositoryInterface
{

    private $storeManager;
    protected $collectionFactory;

    protected $resource;

    protected $dataPartfinderDropdownFactory;

    protected $searchResultsFactory;

    protected $partfinderDropdownFactory;

    protected $dataObjectProcessor;

    protected $dataObjectHelper;

    /**
     * @param ResourcePartfinderDropdown $resource
     * @param PartfinderDropdownFactory $partfinderDropdownFactory
     * @param PartfinderDropdownInterfaceFactory $dataPartfinderDropdownFactory
     * @param collectionFactory $collectionFactory
     * @param PartfinderDropdownSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourcePartfinderDropdown $resource,
        PartfinderDropdownFactory $partfinderDropdownFactory,
        PartfinderDropdownInterfaceFactory $dataPartfinderDropdownFactory,
        collectionFactory $collectionFactory,
        PartfinderDropdownSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->partfinderDropdownFactory = $partfinderDropdownFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPartfinderDropdownFactory = $dataPartfinderDropdownFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface $partfinderDropdown
    ) {
        try {
            $this->resource->save($partfinderDropdown);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the partfinderDropdown: %1',
                $exception->getMessage()
            ));
        }
        return $partfinderDropdown;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($partfinderDropdownId)
    {
        $partfinderDropdown = $this->partfinderDropdownFactory->create();
        $this->resource->load($partfinderDropdown, $partfinderDropdownId);
        if (!$partfinderDropdown->getId()) {
            throw new NoSuchEntityException(
                __(
                    'Dropdown with id "%1" does not exist.',
                    $partfinderDropdownId
                )
            );
        }
        return $partfinderDropdown;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $fields[] = $filter->getField();
                $condition = $filter->getConditionType() ?: 'eq';
                $conditions[] = [$condition => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
        
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface $partfinderDropdown
    ) {
        try {
            $this->resource->delete($partfinderDropdown);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the PartfinderDropdown: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getFinderDropdowns(
        \Webkul\PartFinder\Api\Data\PartfinderInterface $finder,
        $requiredOnly = false
    ) {
        return $this->collectionFactory->create()->getFinderDropdowns(
            $finder->getEntityId(),
            $finder->getStoreId(),
            $requiredOnly
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($partfinderDropdownId)
    {
        return $this->delete($this->getById($partfinderDropdownId));
    }
}
