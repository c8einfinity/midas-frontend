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

use Webkul\PartFinder\Api\PartfinderRepositoryInterface;
use Webkul\PartFinder\Model\ResourceModel\Partfinder as ResourcePartfinder;
use Webkul\PartFinder\Model\ResourceModel\Partfinder\CollectionFactory as PartfinderCollectionFactory;
use Webkul\PartFinder\Api\Data\PartfinderSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class PartfinderRepository implements PartfinderRepositoryInterface
{
    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\Partfinder
     */
    protected $resource;

    /**
     * @var PartfinderFactory
     */
    protected $partfinderFactory;

    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\Partfinder\CollectionFactory
     */
    protected $partfinderCollectionFactory;

    /**
     * @var \Webkul\PartFinder\Api\Data\PartfinderSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @param ResourcePartfinder $resource
     * @param PartfinderFactory $partfinderFactory
     * @param PartfinderCollectionFactory $partfinderCollectionFactory
     * @param PartfinderSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourcePartfinder $resource,
        PartfinderFactory $partfinderFactory,
        PartfinderCollectionFactory $partfinderCollectionFactory,
        PartfinderSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->partfinderFactory = $partfinderFactory;
        $this->partfinderCollectionFactory = $partfinderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Webkul\PartFinder\Api\Data\PartfinderInterface $partfinder
    ) {
        try {
            $this->resource->save($partfinder);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the partfinder: %1',
                $exception->getMessage()
            ));
        }
        return $partfinder;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($partfinderId)
    {
        $partfinder = $this->partfinderFactory->create();
        $this->resource->load($partfinder, $partfinderId);
        if (!$partfinder->getId()) {
            throw new NoSuchEntityException(__('Partfinder with id "%1" does not exist.', $partfinderId));
        }
        return $partfinder;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->partfinderCollectionFactory->create();
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
        \Webkul\PartFinder\Api\Data\PartfinderInterface $partfinder
    ) {
        try {
            $this->resource->delete($partfinder);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Partfinder: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($partfinderId)
    {
        return $this->delete($this->getById($partfinderId));
    }
}
