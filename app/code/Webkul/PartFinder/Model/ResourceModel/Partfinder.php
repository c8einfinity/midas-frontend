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
namespace Webkul\PartFinder\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\App\State;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\PartFinder\Model\ResourceModel\PartfinderCategory;
use Webkul\PartFinder\Model\ResourceModel\ProductSelection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;
use Webkul\PartFinder\Api\Data\PartfinderInterface;
use Webkul\PartFinder\Model\ResourceModel\Partfinder\Website\Link as PartfinderWebsiteLink;

class Partfinder extends AbstractDb
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\PartfinderCategory
     */
    protected $categoryResource;

    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\ProductSelection
     */
    protected $selectionResource;

    /**
     * @param Context $context
     * @param State $state
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param StoreManagerInterface $storeManager
     * @param PartfinderCategory $categoryResource
     * @param ProductSelection $selectionResource
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        State $state,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        StoreManagerInterface $storeManager,
        PartfinderCategory $categoryResource,
        ProductSelection $selectionResource,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->state = $state;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
        $this->categoryResource = $categoryResource;
        $this->selectionResource = $selectionResource;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('partfinder_index', 'entity_id');
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(PartfinderInterface::class)->getEntityConnection();
    }

    /**
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return bool|int|string
     * @throws LocalizedException
     * @throws \Exception
     */
    private function getFinderId(AbstractModel $object, $value, $field = null)
    {
        $entityMetadata = $this->metadataPool->getMetadata(PartfinderInterface::class);
        if (!is_numeric($value) && $field === null) {
            $field = 'identifier';
        } elseif (!$field) {
            $field = $entityMetadata->getIdentifierField();
        }
        $entityId = $value;
        if ($field != $entityMetadata->getIdentifierField()) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $select->reset(Select::COLUMNS)
                ->columns($this->getMainTable() . '.' . $entityMetadata->getIdentifierField())
                ->limit(1);
            $result = $this->getConnection()->fetchCol($select);
            $entityId = count($result) ? $result[0] : false;
        }
        return $entityId;
    }

    /**
     * @return \Magento\Framework\EntityManager\EntityManager
     */
    private function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->entityManager = ObjectManager::getInstance()->get(EntityManager::class);
        }
        return $this->entityManager;
    }
    /**
     * Retrieve finder category identifiers
     *
     * @param \Webkul\PartFinder\Api\Data\PartfinderInterface $finder
     * @return array
     */
    public function getCategoryIds($finder)
    {
        $result = $this->categoryResource->getCategoryLinks($finder);
        return array_column($result, 'category_id');
    }

    /**
     * Retrieve finder product identifiers
     *
     * @param \Webkul\PartFinder\Api\Data\PartfinderInterface $finder
     * @return array
     */
    public function getProductIds($finder)
    {
        $result = $this->selectionResource->getProductLinks($finder);
        return array_column($result, 'entity_id');
    }

    /**
     * delete part finder categories
     *
     * @param \Webkul\PartFinder\Api\Data\PartfinderInterface $finder
     * @param array $categories
     * @return void
     */
    public function deleteCategories($finder, array $categories)
    {
        $this->categoryResource->deleteCategoryLinks($finder, $categories);
    }

    /**
     * delete part finder selection data
     *
     * @param \Webkul\PartFinder\Api\Data\PartfinderInterface $finder
     * @param array $products
     * @return void
     */
    public function deleteProducts($finder, array $products)
    {
        $this->selectionResource->deleteProductLinks($finder, $products);
    }

    /**
     * Save product website relations
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function saveWebsiteIds($finder)
    {
        if ($finder->hasWebsiteIds()) {
            if ($this->storeManager->isSingleStoreMode()) {
                $id = $this->storeManager->getDefaultStoreView()->getWebsiteId();
                $finder->setWebsiteIds([$id]);
            }
            $websiteIds = $finder->getWebsiteIds();
            $finder->setIsChangedWebsites(false);
            $changed = $this->getFinderWebsiteLink()->saveWebsiteIds($finder, $websiteIds);

            if ($changed) {
                $finder->setIsChangedWebsites(true);
            }
        }

        return $this;
    }

    /**
     * @return PartfinderWebsiteLink
     */
    private function getFinderWebsiteLink()
    {
        return ObjectManager::getInstance()->get(PartfinderWebsiteLink::class);
    }

    /**
     * Retrieve finder website identifiers
     * @param \Webkul\PartFinder\Model\Partfinder|int $finder
     * @return array
     */
    public function getWebsiteIds($finder)
    {
        if ($finder instanceof \Webkul\PartFinder\Model\Partfinder) {
            $finderId = $finder->getEntityId();
        } else {
            $finderId = $finder;
        }
        return $this->getFinderWebsiteLink()->getWebsiteIdsByFinderId($finderId);
    }
    
    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $entityMetadata = $this->metadataPool->getMetadata(PartfinderInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = parent::_getLoadSelect($field, $value, $object);
        if ($this->state->getAreaCode() == 'adminhtml') {
            return $select;
        }
        $select->joinInner(
            ['pw' => $this->getTable('partfinder_website')],
            'pw.finder_id = ' . $this->getMainTable() . '.' . $linkField
            . ' AND pw.website_id = ' . $this->storeManager->getWebsite()->getId(),
            []
        )->limit(1);
        
        return $select;
    }

    /**
     * Save entity's attributes into the object's resource
     *
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Exception
     * @since 101.0.0
     */
    public function save(AbstractModel $object)
    {
        $this->getEntityManager()->save($object);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
