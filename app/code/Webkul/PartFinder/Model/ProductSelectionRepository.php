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
use Webkul\PartFinder\Model\ResourceModel\ProductSelection\CollectionFactory as ProductSelectionCollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\PartFinder\Api\Data\ProductSelectionSearchResultsInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\PartFinder\Api\Data\ProductSelectionInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\PartFinder\Api\ProductSelectionRepositoryInterface;
use Magento\Framework\Api\SortOrder;
use Webkul\PartFinder\Model\ResourceModel\ProductSelection as ResourceProductSelection;
use Magento\Framework\Exception\CouldNotSaveException;

class ProductSelectionRepository implements ProductSelectionRepositoryInterface
{

    private $storeManager;
    protected $dataProductSelectionFactory;

    protected $productSelectionFactory;

    protected $resource;

    protected $productSelectionCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectProcessor;

    protected $dataObjectHelper;

    /**
     * @param ResourceProductSelection $resource
     * @param ProductSelectionFactory $productSelectionFactory
     * @param ProductSelectionInterfaceFactory $dataProductSelectionFactory
     * @param ProductSelectionCollectionFactory $productSelectionCollectionFactory
     * @param ProductSelectionSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceProductSelection $resource,
        ProductSelectionFactory $productSelectionFactory,
        ProductSelectionInterfaceFactory $dataProductSelectionFactory,
        ProductSelectionCollectionFactory $productSelectionCollectionFactory,
        ProductSelectionSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->productSelectionFactory = $productSelectionFactory;
        $this->productSelectionCollectionFactory = $productSelectionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataProductSelectionFactory = $dataProductSelectionFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Webkul\PartFinder\Api\Data\ProductSelectionInterface $productSelection
    ) {
        try {
            $this->resource->save($productSelection);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the productSelection: %1',
                $exception->getMessage()
            ));
        }
        return $productSelection;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($productSelectionId)
    {
        $productSelection = $this->productSelectionFactory->create();
        $this->resource->load($productSelection, $productSelectionId);
        if (!$productSelection->getId()) {
            throw new NoSuchEntityException(__('ProductSelection with id "%1" does not exist.', $productSelectionId));
        }
        return $productSelection;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->productSelectionCollectionFactory->create();
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
        \Webkul\PartFinder\Api\Data\ProductSelectionInterface $productSelection
    ) {
        try {
            $this->resource->delete($productSelection);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the ProductSelection: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($productSelectionId)
    {
        return $this->delete($this->getById($productSelectionId));
    }
}
