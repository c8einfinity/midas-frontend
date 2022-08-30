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

use Webkul\PartFinder\Api\PartfinderProductRepositoryInterface;
use Webkul\PartFinder\Api\Data\PartfinderProductSearchResultsInterfaceFactory;
use Webkul\PartFinder\Model\ResourceModel\PartfinderProduct as ResourcePartfinderProduct;
use Webkul\PartFinder\Model\ResourceModel\PartfinderProduct\CollectionFactory as PartfinderProductCollectionFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

class PartfinderProductRepository implements PartfinderProductRepositoryInterface
{
    /**
     * @var \Webkul\PartFinder\Api\Data\PartfinderProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var PartfinderProductFactory
     */
    protected $partfinderProductFactory;

    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\PartfinderProduct
     */
    protected $resource;

    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\PartfinderProduct\CollectionFactory
     */
    protected $partfinderProductCollectionFactory;

    public function __construct(
        PartfinderProductSearchResultsInterfaceFactory $searchResultsFactory,
        PartfinderProductFactory $partfinderProductFactory,
        ResourcePartfinderProduct $resource,
        PartfinderProductCollectionFactory $partfinderProductCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->partfinderProductFactory = $partfinderProductFactory;
        $this->resource = $resource;
        $this->partfinderProductCollectionFactory = $partfinderProductCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Webkul\PartFinder\Api\Data\PartfinderProductInterface $partfinderProduct
    ) {
        try {
            $this->resource->save($partfinderProduct);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the partfinderProduct: %1',
                $exception->getMessage()
            ));
        }
        return $partfinderProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($partfinderProductId)
    {
        $partfinderProduct = $this->partfinderProductFactory->create();
        $this->resource->load($partfinderProduct, $partfinderProductId);
        if (!$partfinderProduct->getId()) {
            throw new NoSuchEntityException(__('PartfinderProduct with id "%1" does not exist.', $partfinderProductId));
        }
        return $partfinderProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->partfinderProductCollectionFactory->create();
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
        \Webkul\PartFinder\Api\Data\PartfinderProductInterface $partfinderProduct
    ) {
        try {
            $this->resource->delete($partfinderProduct);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the PartfinderProduct: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($partfinderProductId)
    {
        return $this->delete($this->getById($partfinderProductId));
    }
}
