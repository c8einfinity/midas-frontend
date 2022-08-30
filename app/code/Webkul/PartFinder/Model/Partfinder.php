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

use Magento\Framework\Model\AbstractModel;
use Webkul\PartFinder\Api\Data\PartfinderInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Webkul\PartFinder\Model\ResourceModel\Partfinder as PartFinderResource;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\PartFinder\Model\ResourceModel\Partfinder\Collection;
use Webkul\PartFinder\Model\Partfinder\PartfinderDropdownFactory;
use Webkul\PartFinder\Api\Data\PartfinderCategoryInterfaceFactory;
use Webkul\PartFinder\Api\PartfinderCategoryRepositoryInterface;
use Webkul\PartFinder\Api\Data\ProductSelectionInterfaceFactory;
use Webkul\PartFinder\Api\ProductSelectionRepositoryInterface;
use Webkul\PartFinder\Model\ProductSelection;
use Webkul\PartFinder\Model\Partfinder\PartfinderDropdown;

class Partfinder extends AbstractModel implements PartfinderInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'webkul_partfinder_partfinder';

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepositry;

    /**
     * @var \Webkul\PartFinder\Model\Partfinder\PartfinderDropdownFactory
     */
    protected $dropdownFactory;

    /**
     * @var \Webkul\PartFinder\Api\Data\PartfinderCategoryInterfaceFactory
     */
    protected $categoryFactory;
    
    /**
     * @var \Webkul\PartFinder\Api\PartfinderCategoryRepositoryInterface
     */
    protected $categoryRepository;
    
    /**
     * @var \Webkul\PartFinder\Api\Data\ProductSelectionInterfaceFactory
     */
    protected $productSelectionFactory;
    
    /**
     * @var \Webkul\PartFinder\Api\ProductSelectionRepositoryInterface
     */
    protected $productSelectionRepo;
    

    /**
     * @param Context $context
     * @param Registry $registry
     * @param PartFinderResource $resource
     * @param Collection $resourceCollection
     * @param ProductRepositoryInterface $productRepositry
     * @param PartfinderDropdownFactory $dropdownFactory
     * @param PartfinderCategoryInterfaceFactory $categoryFactory
     * @param PartfinderCategoryRepositoryInterface $categoryRepository
     * @param ProductSelectionInterfaceFactory $productSelectionFactory
     * @param ProductSelectionRepositoryInterface $productSelectionRepo
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PartFinderResource $resource,
        Collection $resourceCollection,
        ProductRepositoryInterface $productRepositry,
        PartfinderDropdownFactory $dropdownFactory,
        PartfinderCategoryInterfaceFactory $categoryFactory,
        PartfinderCategoryRepositoryInterface $categoryRepository,
        ProductSelectionInterfaceFactory $productSelectionFactory,
        ProductSelectionRepositoryInterface $productSelectionRepo,
        \Webkul\PartFinder\Model\ResourceModel\DropdownOption\CollectionFactory $dropdownOption,
        \Webkul\PartFinder\Model\DropdownOptionFactory $dropdownOptionModel,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->productRepositry = $productRepositry;
        $this->dropdownFactory = $dropdownFactory;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->productSelectionFactory = $productSelectionFactory;
        $this->productSelectionRepository = $productSelectionRepo;
        $this->dropdownOption = $dropdownOption;
        $this->dropdownOptionModel = $dropdownOptionModel;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\PartFinder\Model\ResourceModel\Partfinder::class);
    }

    /**
     * Get entity_id
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set name
     * @param string $name
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get widget name
     * @return string|null
     */
    public function getWidgetName()
    {
        return $this->getData(self::WIDGET_NAME);
    }

    /**
     * Set widget name
     * @param string $name
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setWidgetName($name)
    {
        return $this->setData(self::WIDGET_NAME, $name);
    }

    /**
     * Get widget code
     * @return string|null
     */
    public function getWidgetCode()
    {
        return $this->getData(self::WIDGET_CODE);
    }

    /**
     * Set widget code
     * @param string $code
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setWidgetCode($code)
    {
        return $this->setData(self::WIDGET_CODE, $code);
    }

    /**
     * Get status
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get created_at
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get store_id
     * @return string
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set store_id
     * @param string $storeId
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get dropdown_count
     * @return string|null
     */
    public function getDropdownCount()
    {
        return $this->getData(self::DROPDOWN_COUNT);
    }

    /**
     * Set dropdown_count
     * @param int $count
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setDropdownCount($count)
    {
        return $this->setData(self::DROPDOWN_COUNT, $count);
    }

    /**
     * Retrieve assigned category Ids
     *
     * @return array
     */
    public function getCategoryIds()
    {
        if (!$this->hasData('category_ids')) {
            $ids = $this->_getResource()->getCategoryIds($this);
            $this->setData('category_ids', $ids);
        }

        return (array) $this->_getData('category_ids');
    }

    /**
     * return saved categories
     *
     * @return array
     */
    protected function getOldCategories()
    {
        return $this->_getResource()->getCategoryIds($this);
    }

    /**
     * Save $finder's categories
     *
     * @param array $categories
     * @return Partfinder
     */
    public function processCategories(array $categories)
    {
        $assignedCategories = $this->getOldCategories();
        $deleteCategories  = array_diff($assignedCategories, $categories);
        if (!empty($deleteCategories)) {
            $this->_getResource()->deleteCategories($this, $deleteCategories);
        }
        foreach (array_diff($categories, $assignedCategories) as $categoryId) {
            $category = $this->categoryFactory->create();
            $category->setFinderId($this->getId());
            $category->setCategoryId($categoryId);
            $this->saveObject($this->categoryRepository, $category);
        }
        return $this;
    }

    /**
     * delete finder variation from admin product edit page
     *
     * @param array $selection
     * @return void
     */
    private function deleteAdminProduct(array $selection)
    {
        $selectionModel = $this->productSelectionFactory->create();
        $savedSelectionIds = $selectionModel->getResource()->getSelectionIds(
            $selection
        );
        
        foreach ($savedSelectionIds as $selectionId) {
            $this->productSelectionRepository->deleteById($selectionId);
        }
    }

    /**
     * save finder product variation
     *
     * @param array $selections
     * @return void
     */
    private function saveSelections(array $selections)
    {
        $selectionModel = $this->productSelectionFactory->create();
        $selectionModel->setData($selections);
        $this->productSelectionRepository->save($selectionModel);
    }
    
    /**
     * Save product from admin product edit page
     *
     * @param array $products
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function productEditSave(array $products)
    {
        $selectionAllData = [];
        $selectionModel = $this->productSelectionFactory->create();
        foreach ($products as $product) {
            $selectionData = $this->createProductSelection($product);
            $this->deleteAdminProduct($selectionData);
            $selectionAllData[] = $selectionData;
        }
        
        foreach ($selectionAllData as $data) {
            $this->saveSelections($data);
        }
        return $this;
    }

    /**
     * delete finder variation
     *
     * @param array $selection
     * @return void
     */
    private function deleteSelection(array $selection)
    {
        $savedSelectionIds = $this->_getResource()->getProductIds($this);
        foreach ($savedSelectionIds as $selectionId) {
            $this->productSelectionRepository->deleteById($selectionId);
        }
    }
    /**
     * Save part finder product selection
     *
     * @param array $products
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function processManualProducts(array $products)
    {
        if (empty($products)) {
            $this->getSelectionInstance()->_getResource()->deleteByFinderId($this->getId());
        }

        //custom code starts

        $selectionAllData = [];
        $selectionModel = $this->productSelectionFactory->create();
        $productIds = [];
        
        $dropdown = $this->dropdownFactory->create()
                         ->getCollection()
                         ->addFieldToFilter(
                             "finder_id",
                             $this->getId()
                         );
        
        foreach ($dropdown as $ddn) {
            $key = $ddn->getLabel();
            $value = $ddn->getEntityId();
            $dropdownValues[$key] = $value;
        }
        $productData = [];
        foreach ($products as $product) {
            if (array_key_exists("selected_options", $product)) {
                $productData = json_decode($product['selected_options']);
                
                foreach ($productData as $eachProduct) {
                    $checkCollection = $this->dropdownOption->create()
                                            ->addFilterToData(
                                                "pfd.finder_id",
                                                $this->getId()
                                            )
                                            ->addFilterToData(
                                                "main_table.label",
                                                $eachProduct->label
                                            );

                    if ($checkCollection->getSize() < 1) {
                        $arr = [
                            'dropdown_id'=>$dropdownValues[$eachProduct->dropdown_label],
                            'label'=>$eachProduct->label,
                            'value'=> $eachProduct->value
                        ];
                        $this->dropdownOptionModel->create()
                            ->setData($arr)
                            ->save();
                    }
                }
            }
        }

        //custom code ends
        
        foreach ($products as $product) {
            $selectionData = $this->createProductSelection($product);
            $productIds[] = $selectionData['product_id'];
            $selectionAllData[] = $selectionData;
        }
        $this->deleteSelection($productIds);
        foreach ($selectionAllData as $data) {
            $this->saveSelections($data);
        }
        return $this;
    }

    /**
     * Create product array to save/update
     *
     * @param array $product
     * @return array
     */
    private function createProductSelection(array $product)
    {
        $productData = [];
        $selections = explode('-', $product['variationKey']);
        
        $tempVariation = [];
        $dropdowns = $this->getDropdownsCollection();
        foreach ($dropdowns as $dropdown) {
            $options = $dropdown->getOptions();
            if (!empty($options)) {
                foreach ($options as $option) {
                    if (in_array($option->getValue(), $selections, true)) {
                        $tempVariation[] = $option->getId();
                    }
                }
            }
        }
        $productId = 0;
        if (!isset($product['product_id'])) {
            $product = $this->productRepositry->get($product['sku']);
            $productId = $product->getId();
        } elseif (isset($product['product_id'])) {
            $productId = $product['product_id'];
        }
        $productData = [
            'product_id' => $productId,
            'finder_id' => $this->getId(),
            'variation_key' => implode('-', $tempVariation)
        ];
        return $productData;
    }

    /**
     * Add option to array of product options
     *
     * @param Partfinder\PartfinderDropdown $dropdown
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function addDropdowns(Partfinder\PartfinderDropdown $dropdown)
    {
        $dropdowns = (array)$this->getData('dropdowns');
        $dropdowns[] = $dropdown;
        $this->setDropdowns($dropdowns);
        return $this;
    }

    /**
     * @param \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface[] $dropdowns
     * @return $this
     */
    public function setDropdowns(array $dropdowns = null)
    {
        $this->setData('dropdowns', $dropdowns);
        return $this;
    }

    /**
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface[]
     */
    public function getDropdowns()
    {
        return $this->getData('dropdowns');
    }

    /**
     * dropdowns to save.
     *
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function processDropdowns()
    {
        foreach ($this->getDropdowns() as $dropdowns) {
            if (is_object($dropdowns)) {
                $dropdowns->setFinderId($this->getId());
                $this->saveDropdown($dropdowns);
            }
        }

        return $this;
    }

    /**
     *
     * @param PartfinderDropdown $dropdown
     * @return void
     */
    protected function saveDropdown(PartfinderDropdown $dropdown)
    {
        $dropdown->save();
    }

    /**
     *
     * @param PartfinderDropdown $dropdown
     * @return void
     */
    public function saveWebsites()
    {
        $this->_getResource()->saveWebsiteIds($this);
    }

    /**
     * Saving part finder dropdowns after save
     *
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function afterSave()
    {
        $this->processDropdowns();
        $result = parent::afterSave();
        return $result;
    }

    /**
     * Retrieve dropdown instance
     *
     * @return Partfinder\PartfinderDropdown
     */
    public function getDropdownInstance()
    {
        if (!isset($this->dropdownInstance)) {
            $this->dropdownInstance = $this->dropdownFactory->create();
            $this->dropdownInstance->setFinder($this);
        }
        return $this->dropdownInstance;
    }

    /**
     * Retrieve dowpdown collection of finder
     *
     * @return \Webkul\PartFinder\Model\ResourceModel\PartfinderDropdown\Collection
     */
    public function getDropdownsCollection()
    {
        return $this->getDropdownInstance()->getDropdownCollection($this);
    }

    /**
     * Retrieve product selection instance
     *
     * @return ProductSelection
     */
    public function getSelectionInstance()
    {
        if (!isset($this->selectionInstance)) {
            $this->selectionInstance = $this->productSelectionFactory->create();
            $this->selectionInstance->setFinder($this);
        }
        return $this->selectionInstance;
    }

    /**
     * Retrieve dowpdown collection of finder
     *
     * @return \Webkul\PartFinder\Model\ResourceModel\PartfinderDropdown\Collection
     */
    public function getManualSelectionCollection()
    {
        return $this->getSelectionInstance()->getManualCollection($this);
    }

    /**
     * Retrieve finder websites identifiers
     *
     * @return array
     */
    public function getWebsiteIds()
    {
        if (!$this->hasWebsiteIds()) {
            $ids = $this->_getResource()->getWebsiteIds($this);
            $this->setWebsiteIds($ids);
        }
        return $this->getData('website_ids');
    }

    /**
     * Save Object
     *
     * @param object $repository
     * @param object $model
     * @return void
     */
    public function saveObject($repository, $model)
    {
        $repository->save($model);
    }
}
