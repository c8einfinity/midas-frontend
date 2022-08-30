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
namespace Webkul\PartFinder\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\PartFinder\Model\ResourceModel\DropdownOption as ResourceDropdownOption;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\PartFinder\Api\Data\DropdownOptionSearchResultsInterfaceFactory;
use Webkul\PartFinder\Api\DropdownOptionRepositoryInterface;
use Webkul\PartFinder\Model\ResourceModel\DropdownOption\CollectionFactory as DropdownOptionCollectionFactory;
use Webkul\PartFinder\Api\Data\DropdownOptionInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;

class DropdownOptionRepository implements DropdownOptionRepositoryInterface
{

    protected $dataObjectHelper;

    protected $dropdownOptionCollectionFactory;

    protected $resource;

    protected $dropdownOptionFactory;

    protected $searchResultsFactory;

    protected $dataDropdownOptionFactory;

    protected $dataObjectProcessor;

    private $storeManager;

    /**
     * @param ResourceDropdownOption $resource
     * @param DropdownOptionFactory $dropdownOptionFactory
     * @param DropdownOptionInterfaceFactory $dataDropdownOptionFactory
     * @param DropdownOptionCollectionFactory $dropdownOptionCollectionFactory
     * @param DropdownOptionSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceDropdownOption $resource,
        DropdownOptionFactory $dropdownOptionFactory,
        DropdownOptionInterfaceFactory $dataDropdownOptionFactory,
        DropdownOptionCollectionFactory $dropdownOptionCollectionFactory,
        DropdownOptionSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->dropdownOptionFactory = $dropdownOptionFactory;
        $this->dropdownOptionCollectionFactory = $dropdownOptionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataDropdownOptionFactory = $dataDropdownOptionFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Webkul\PartFinder\Api\Data\DropdownOptionInterface $dropdownOption
    ) {
        try {
            $dropdownOption->setDropdownId((int) $dropdownOption->getDropdownId());
            $this->resource->save($dropdownOption);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the dropdownOption: %1',
                $exception->getMessage()
            ));
        }
        return $dropdownOption;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($dropdownOptionId)
    {
        $dropdownOption = $this->dropdownOptionFactory->create();
        $this->resource->load($dropdownOption, $dropdownOptionId);
        if (!$dropdownOption->getId()) {
            throw new NoSuchEntityException(__('DropdownOption with id "%1" does not exist.', $dropdownOptionId));
        }
        return $dropdownOption;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->dropdownOptionCollectionFactory->create();
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
    public function getDropdownOptions(
        \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface $dropdown
    ) {
        return $this->dropdownOptionCollectionFactory->create()->getDropdownOptions(
            $dropdown->getEntityId()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Webkul\PartFinder\Api\Data\DropdownOptionInterface $dropdownOption
    ) {
        try {
            $this->resource->delete($dropdownOption);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the DropdownOption: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($dropdownOptionId)
    {
        return $this->delete($this->getById($dropdownOptionId));
    }
}
