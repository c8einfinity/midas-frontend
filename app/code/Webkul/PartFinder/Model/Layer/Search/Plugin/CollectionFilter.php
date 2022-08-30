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
namespace Webkul\PartFinder\Model\Layer\Search\Plugin;

use Magento\Catalog\Model\Category;
use Webkul\PartFinder\Model\FinderQueryFactory;
use Webkul\PartFinder\Model\PartfinderFactory;
use Webkul\PartFinder\Model\ResourceModel\ProductSelection\CollectionFactory;

class CollectionFilter
{
    /**
     * @var \Webkul\PartFinder\Model\FinderQueryFactory
     */
    protected $finderQueryFactory;

    /**
     * @var \Webkul\PartFinder\Model\PartfinderFactory
     */
    protected $finderFactory;

    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\ProductSelection\CollectionFactory
     */
    protected $selectionCollectionFactory;
    
    /**
     * @param FinderQueryFactory $finderQueryFactory
     * @param PartfinderFactory $finderFactory
     * @param CollectionFactory $selectionCollectionFactory
     */
    public function __construct(
        FinderQueryFactory $finderQueryFactory,
        PartfinderFactory $finderFactory,
        CollectionFactory $selectionCollectionFactory
    ) {
        $this->finderQueryFactory = $finderQueryFactory;
        $this->finderFactory = $finderFactory;
        $this->selectionCollectionFactory = $selectionCollectionFactory;
    }

    /**
     * Add search filter criteria to search collection
     *
     * @param \Magento\Catalog\Model\Layer\Search\CollectionFilter $subject
     * @param null $result
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * @param Category $category
     * @return void
     */
    public function afterFilter(
        \Magento\Catalog\Model\Layer\Search\CollectionFilter $subject,
        $result,
        $collection,
        Category $category
    ) {
        /** @var \Webkul\PartFinder\Model\FinderQuery $finderQuery */
        $finderQuery = $this->finderQueryFactory->get();
        if (!$finderQuery->isQueryTextShort() && $finderQuery->getQueryParam() == 'finder') {
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
            $finders = [];
            foreach ($finderIds as $finderId) {
                $finderModel = $this->finderFactory->create();
                $finder = $this->loadObject($finderModel, $finderId);
                if ($finder->getId()) {
                    $finders[] = $finder->getId();
                }
            }
            $selectionCollection->addFieldToFilter(
                'finder_id',
                ['in' => $finders]
            );
            if ($selectionCollection->getSize()) {
                $collection->getSelect()
                ->where("e.entity_id IN(".implode(',', $selectionCollection->getAllProductIds()).")");
            } else {
                $collection->getSelect()
                    ->where("e.entity_id IN(0)");
            }
        }
    }

    /**
     * Load Object by ID
     *
     * @param object $model
     * @param int $id
     * @return object $object
     */
    protected function loadObject($model, $id)
    {
        $object = $model->load($id);
        return $object;
    }
}
