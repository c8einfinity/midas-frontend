<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_PartFinder
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\PartFinder\Model;

use Webkul\PartFinder\Api\ProfileDataRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\PartFinder\Api\Data\ProfileDataInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\PartFinder\Model\ResourceModel\ProfileData as ResourceProfileData;
use Magento\Framework\Api\SortOrder;
use Webkul\PartFinder\Model\ResourceModel\ProfileData\CollectionFactory as ProfileDataCollectionFactory;
use Webkul\PartFinder\Api\Data\ProfileDataSearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;

class ProfileDataRepository implements ProfileDataRepositoryInterface
{

    protected $dataObjectHelper;

    protected $profileDataFactory;

    protected $resource;

    protected $searchResultsFactory;

    protected $profileDataCollectionFactory;

    protected $dataProfileDataFactory;

    protected $dataObjectProcessor;

    private $storeManager;

    /**
     * @param ResourceProfileData $resource
     * @param ProfileDataFactory $profileDataFactory
     * @param ProfileDataInterfaceFactory $dataProfileDataFactory
     * @param ProfileDataCollectionFactory $profileDataCollectionFactory
     * @param ProfileDataSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceProfileData $resource,
        ProfileDataFactory $profileDataFactory,
        ProfileDataInterfaceFactory $dataProfileDataFactory,
        ProfileDataCollectionFactory $profileDataCollectionFactory,
        ProfileDataSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->profileDataFactory = $profileDataFactory;
        $this->profileDataCollectionFactory = $profileDataCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataProfileDataFactory = $dataProfileDataFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Webkul\PartFinder\Api\Data\ProfileDataInterface $profileData
    ) {
        try {
            $this->resource->save($profileData);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the profileData: %1',
                $exception->getMessage()
            ));
        }
        return $profileData;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($profileDataId)
    {
        $profileData = $this->profileDataFactory->create();
        $this->resource->load($profileData, $profileDataId);
        if (!$profileData->getId()) {
            throw new NoSuchEntityException(__('ProfileData with id "%1" does not exist.', $profileDataId));
        }
        return $profileData;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->profileDataCollectionFactory->create();
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
        \Webkul\PartFinder\Api\Data\ProfileDataInterface $profileData
    ) {
        try {
            $this->resource->delete($profileData);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the ProfileData: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($profileDataId)
    {
        return $this->delete($this->getById($profileDataId));
    }
}
