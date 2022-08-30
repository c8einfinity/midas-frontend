<?php


namespace Motus\Tools\Block\Widget;


use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Setup\Exception;
use Magento\Widget\Block\BlockInterface;

class MotusPartsFinder extends Template implements BlockInterface
{
    protected $_template = "widget/motus_parts_finder.phtml";

    /*Product collection variable*/
    protected $_productCollection;

    protected $_imageBuilder;

    protected $stockFilter;

    protected $_attributeRespository;

    protected $_DBA;

    protected $_categoryRepository;

    protected $_productRepository;

    protected $_categoryFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,

        array $data = []
    )
    {
        $this->_productCollection= $productCollection;
        $this->stockFilter = $stockFilter;
        $this->_imageBuilder = $imageBuilder;
        $this->_attributeRespository = $attributeRepository;
        $this->_categoryRepository = $categoryRepository;
        $this->_productRepository = $productRepository;
        $this->_categoryFactory = $categoryFactory;
        parent::__construct($context, $data);
    }

    public function getImage($product, $imageId)
    {
        return $this->_imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->create();
    }

    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }

    public function getCategoryImage($categoryId)
    {
        $categoryIdElements = explode('-', $categoryId);
        $category           = $this->_categoryRepository->get(end($categoryIdElements));
        $categoryImage       = $category->getImageUrl();

        return $categoryImage;
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional the route params
     * @return string
     */
    public function getProductUrl($product, $additional = [])
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }

        return '#';
    }

    /**
     * Initialize Tina4
     */
    public function initTina4() {
        $env = include ("./app/etc/env.php");
        if (!defined("TINA4_INCLUDE_LOCATIONS")) {
            define("TINA4_INCLUDE_LOCATIONS", ["./system/src/objects"]);
            global $DBA;
            $DBA = new \Tina4\DataMySQL($env["db"]["connection"]["default"]["host"] . ":" . $env["db"]["connection"]["default"]["dbname"], $env["db"]["connection"]["default"]["username"], $env["db"]["connection"]["default"]["password"]);
            $this->_DBA = $DBA;
        }
    }

    public function getProductCollection()
    {
        $this->initTina4();
        $filters = [];
        $names = [];
        $canFilter = false;

        foreach ($_REQUEST as $name => $value) {
            if (strpos($name, "select") !== false) {
                $lookup = $this->_DBA->fetch("select * from motus_category_lookup where id = {$value}")->AsArray();
                if (!empty($lookup)) {

                    $filters = explode (";", $lookup[0]["lookupReference"]); //take the last set always
                    foreach ($filters as $fid => $filter) {
                        if (empty($filter)) {
                            unset($filters[$fid]);
                        }
                    }
                    $names[] = $lookup[0]["name"];
                }
                $canFilter = true;
            }
        }

        if (!$canFilter) return null;

        $collection = $this->_productCollection->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

        $filterStatement = [];
        foreach ($names as $id => $name) {
            $filterStatement[] = ['attribute' => 'name', 'like' => "%{$name}%"];
        }

        //$filterLookup = [];
        $filterStatement[] = ['attribute' => 'filter_options', 'like' => "%Generic%"];
        foreach ($filters as $id => $filter) {
            $filterStatement[] = ['attribute' => 'filter_options', 'like' => "%{$filter}%"];
        }

        echo "<!--";
        print_r ($filterStatement);
        //print_r ($filterLookup);
        echo "-->";
        if (!empty($filterStatement) && $canFilter) {
            try {
                $collection->addAttributeToFilter($filterStatement);
            } catch (\Exception $e) {

            }
        }


        if (!empty($filterLookup) && $canFilter) {
            try {
                if ($this->_attributeRespository->get('filter_options') !== "") {
                    $collection->addAttributeToFilter($filterLookup);
                }
            } catch (NoSuchEntityException $e) {
            }
        }

        if (isset($_REQUEST["attributeId"]) && !empty($_REQUEST["attributeId"])) {
            $category = $this->_categoryFactory->create()->load($_REQUEST["attributeId"]);
            $collection->addCategoryFilter($category);
        }
            else
        if (isset($_REQUEST["categoryId"]) && !empty($_REQUEST["categoryId"])) {
            $category = $this->_categoryFactory->create()->load($_REQUEST["categoryId"]);
            $collection->addCategoryFilter($category);
        }

        // ADD THIS CODE IF YOU WANT IN-STOCK-PRODUCT
        $this->stockFilter->addInStockFilterToCollection($collection);





        return $collection;
    }

    /**
     * Check Product has URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }

    public function getFilters($category){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $filterableAttributes = $objectManager->getInstance()->get(\Magento\Catalog\Model\Layer\Category\FilterableAttributeList::class);

        $appState = $objectManager->getInstance()->get(\Magento\Framework\App\State::class);
        $layerResolver = $objectManager->getInstance()->get(\Magento\Catalog\Model\Layer\Resolver::class);
        $filterList = $objectManager->getInstance()->create(
            \Magento\Catalog\Model\Layer\FilterList::class,
            [
                'filterableAttributes' => $filterableAttributes
            ]
        );

        $layer = $layerResolver->get();
        $layer->setCurrentCategory($category);
        $filters = $filterList->getFilters($layer);
        $maxPrice = $layer->getProductCollection()->getMaxPrice();
        $minPrice = $layer->getProductCollection()->getMinPrice();

        $filterArray = [];

        $i = 0;
        foreach($filters as $filter)
        {
            //$availablefilter = $filter->getRequestVar(); //Gives the request param name such as 'cat' for Category, 'price' for Price
            $availablefilter = (string)$filter->getName(); //Gives Display Name of the filter such as Category,Price etc.
            $items = $filter->getItems(); //Gives all available filter options in that particular filter
            $filterValues = array();
            $j = 0;
            foreach($items as $item)
            {
                $filterValues[$j]['display'] = strip_tags($item->getLabel());
                $filterValues[$j]['value']   = $item->getValue();
                $filterValues[$j]['name'] = $item->getName();
                $filterValues[$j]['count']   = $item->getCount(); //Gives no. of products in each filter options
                $j++;
            }


            if(!empty($filterValues) && count($filterValues)>1)
            {
                $filterArray['availablefilter'][$availablefilter] =  $filterValues;
            }
            $i++;
        }


        return $filterArray;

    }

}
