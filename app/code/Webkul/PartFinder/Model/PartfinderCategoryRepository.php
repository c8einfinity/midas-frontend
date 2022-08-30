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

use Webkul\PartFinder\Api\PartfinderCategoryRepositoryInterface;
use Webkul\PartFinder\Model\ResourceModel\PartfinderCategory as ResourcePartfinderCategory;
use Webkul\PartFinder\Model\ResourceModel\PartfinderCategory\CollectionFactory as PartfinderCategoryCollectionFactory;
use Webkul\PartFinder\Api\Data\PartfinderCategorySearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class PartfinderCategoryRepository implements PartfinderCategoryRepositoryInterface
{
    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\PartfinderCategory
     */
    protected $resource;

    /**
     * @var PartfinderCategoryFactory
     */
    protected $partfinderCategoryFactory;

    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\PartfinderCategory\CollectionFactory
     */
    protected $partfinderCategoryCollectionFactory;

    /**
     * @var \Webkul\PartFinder\Api\Data\PartfinderCategorySearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @param ResourcePartfinderCategory $resource
     * @param PartfinderCategoryFactory $partfinderCategoryFactory
     * @param PartfinderCategoryCollectionFactory $partfinderCategoryCollectionFactory
     * @param PartfinderCategorySearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourcePartfinderCategory $resource,
        PartfinderCategoryFactory $partfinderCategoryFactory,
        PartfinderCategoryCollectionFactory $partfinderCategoryCollectionFactory,
        PartfinderCategorySearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->partfinderCategoryFactory = $partfinderCategoryFactory;
        $this->partfinderCategoryCollectionFactory = $partfinderCategoryCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface $partfinderCategory
    ) {
        try {
            $this->resource->save($partfinderCategory);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the partfinderCategory: %1',
                $exception->getMessage()
            ));
        }
        return $partfinderCategory;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($partfinderCategoryId)
    {
        $partfinderCategory = $this->partfinderCategoryFactory->create();
        $this->resource->load($partfinderCategory, $partfinderCategoryId);
        if (!$partfinderCategory->getId()) {
            throw new NoSuchEntityException(
                __('PartfinderCategory with id "%1" does not exist.', $partfinderCategoryId)
            );
        }
        return $partfinderCategory;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->partfinderCategoryCollectionFactory->create();
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
        \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface $partfinderCategory
    ) {
        try {
            $this->resource->delete($partfinderCategory);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the PartfinderCategory: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($partfinderCategoryId)
    {
        return $this->delete($this->getById($partfinderCategoryId));
    }
}
