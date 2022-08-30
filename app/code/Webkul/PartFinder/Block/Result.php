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
namespace Webkul\PartFinder\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\CatalogSearch\Helper\Data;
use Magento\Search\Model\QueryFactory;
use Webkul\PartFinder\Model\FinderQueryFactory;
use Webkul\PartFinder\Model\ResourceModel\ProductSelection\CollectionFactory;
use Webkul\PartFinder\Api\PartfinderRepositoryInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\Serialize\Serializer\Json as JsonData;

/**
 * Product search result block
 *
 * @api
 * @since 100.0.2
 */
class Result extends \Magento\CatalogSearch\Block\Result
{
    /**
     * @var \Webkul\PartFinder\Model\FinderQueryFactory
     */
    protected $finderQueryFactory;

    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\ProductSelection\CollectionFactory
     */
    protected $selectionCollectionFactory;

    /**
     * @var \Webkul\PartFinder\Api\PartfinderRepositoryInterface
     */
    protected $partFinderRepository;

    /**
     * Catalog Product collection
     *
     * @var Collection
     */
    protected $productCollection;

    /**
     * @param Context $context
     * @param LayerResolver $layerResolver
     * @param Data $catalogSearchData
     * @param QueryFactory $queryFactory
     * @param FinderQueryFactory $finderQueryFactory
     * @param CollectionFactory $selectionCollectionFactory
     * @param PartfinderRepositoryInterface $partFinderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        LayerResolver $layerResolver,
        Data $catalogSearchData,
        QueryFactory $queryFactory,
        FinderQueryFactory $finderQueryFactory,
        CollectionFactory $selectionCollectionFactory,
        JsonData $jsonData,
        PartfinderRepositoryInterface $partFinderRepository,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $layerResolver,
            $catalogSearchData,
            $queryFactory,
            $data
        );
        $this->finderQueryFactory = $finderQueryFactory;
        $this->selectionCollectionFactory = $selectionCollectionFactory;
        $this->jsonData = $jsonData;
        $this->partFinderRepository = $partFinderRepository;
    }

    /**
     * Retrieve query model object
     *
     * @return \Magento\Search\Model\Query
     */
    protected function _getQuery()
    {
        return $this->finderQueryFactory->get();
    }

    /**
     * Get Finder Ids
     *
     * @return array $finderIds
     */
    public function getFinderIds()
    {
        $finderQuery = $this->_getQuery();
        $finderIds = [];
        if ($finderQuery->getQueryParam() == 'finder') {
            $filters = explode('_', $finderQuery->getQueryText());
            $selectionCollection = $this->selectionCollectionFactory->create();
            $conditions = [];
            foreach ($filters as $filter) {
                $conditions[] = [
                    'like' => "$filter%"
                ];
            }
            $selectionCollection->addFieldToFilter(
                ['variation_key'],
                [$conditions]
            );
            $finderIds = array_unique($selectionCollection->getAllFinderIds());
        }
        return $finderIds;
    }

    /**
     * Return finder model
     *
     * @param int|null $finderId
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function getFinder($finderId)
    {
        try {
            return $this->partFinderRepository->getById($finderId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * Get each part finder dropdown collection
     *
     * @param int $finderId
     * @return array|bool
     */
    public function getDropdowns($finderId)
    {
        $finder = $this->getFinder($finderId);
        if ($finder) {
            $items = $finder->getDropdownsCollection();
            return $items->getItems();
        }
        return false;
    }

    /**
     * Get Dropdown options
     *
     * @return array
     */
    public function getDropdownOptions($finderId)
    {
        $dropdownData = [];
        if ($this->getDropdowns($finderId)) {
            foreach ($this->getDropdowns($finderId) as $dropdown) {
                $dropdownOptions = [];
                if (empty($dropdown->getOptions())) {
                    continue;
                }
                foreach ($dropdown->getOptions() as $option) {
                    $dropdownOptions[] = [
                        'option_id' => $option->getId(),
                        'value' => $option->getValue(),
                        'label' => $option->getLabel()
                    ];
                }
                $dropdownData[] = [
                    'dropdown_id' => $dropdown->getId(),
                    'sort_order' => $dropdown->getSortOrder(),
                    'label' => $dropdown->getLabel(),
                    'options' => $dropdownOptions,
                    'is_required' => (bool) $dropdown->getIsRequired()
                ];
            }
        }
        
        return $dropdownData;
    }

    /**
     * Get part finder product variations
     *
     * @return array
     */
    public function getVariations($finderId)
    {
        $tempVariations = [];
        $variations = [];
        $finder = $this->getFinder($finderId);
        if ($finder) {
            $items = $finder->getManualSelectionCollection();
            foreach ($items as $selection) {
                $variationKeys = explode('-', $selection->getVariationKey());
                foreach ($variationKeys as $key => $value) {
                    if (isset($variationKeys[$key+1])) {
                        $variations[$value][] = $variationKeys[$key+1];
                    }
                }
            }
            foreach ($variations as $key => $value) {
                $unique = array_unique($value);
                $variations[$key] = array_values($unique);
            }
        }
        return $variations;
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $title = $this->getSearchQueryText();
        $this->pageConfig->getTitle()->set($title);
        // add Home breadcrumb
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            )->addCrumb(
                'search',
                ['label' => $title, 'title' => $title]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve additional blocks html
     *
     * @return string
     */
    public function getAdditionalHtml()
    {
        return $this->getLayout()->getBlock('finder_result_list')->getChildHtml('additional');
    }

    /**
     * Retrieve search list toolbar block
     *
     * @return ListProduct
     */
    public function getListBlock()
    {
        return $this->getChildBlock('finder_result_list');
    }

    /**
     * Retrieve Search result list HTML output
     *
     * @return string
     */
    public function getProductListHtml()
    {
        return $this->getChildHtml('finder_result_list');
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Collection
     */
    protected function _getProductCollection()
    {
        if (null === $this->productCollection) {
            $this->productCollection = $this->getListBlock()->getLoadedProductCollection();
        }
        return $this->productCollection;
    }

    /**
     * Get search query text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSearchQueryText()
    {
        $finderNames = '';
        foreach ($this->getFinderIds() as $finderId) {
            $finder = $this->getFinder($finderId);
            if (!$finder) {
                continue;
            }
            if ($finderNames == '') {
                $finderNames = $this->escapeHtml($finder->getWidgetName());
            } else {
                $finderNames.= '& '.$this->escapeHtml($finder->getWidgetName());
            }
        }
        return __("Search results for: '%1'", $finderNames);
    }

    /**
     *  Encode the array to json
     *
     * @params array @array
     * @return array
     */
    public function jsonEncode($array)
    {
        return $this->jsonData->serialize($array);
    }
}
