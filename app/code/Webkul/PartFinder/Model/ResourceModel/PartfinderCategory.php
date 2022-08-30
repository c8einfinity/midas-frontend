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

use Webkul\PartFinder\Api\Data\PartfinderInterface;

class PartfinderCategory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('partfinder_category', 'entity_id');
    }

    /**
     * Retrieve finder category links by PartfinderInterface and category identifiers
     *
     * @param PartfinderInterface $finder
     * @param array $categoryIds
     * @return array
     */
    public function getCategoryLinks(PartfinderInterface $finder, array $categoryIds = [])
    {
        
        $connection = $this->getConnection();

        $select = $connection->select();
        $select->from($this->getMainTable(), ['category_id']);
        $select->where('finder_id = ?', (int)$finder->getId());

        if (!empty($categoryIds)) {
            $select->where('category_id IN(?)', $categoryIds);
        }

        $result = $connection->fetchAll($select);
        return $result;
    }

    /**
     * @param PartfinderInterface $finder
     * @param array $deleteLinks
     * @return array
     */
    public function deleteCategoryLinks(PartfinderInterface $finder, array $deleteLinks)
    {
        if (empty($deleteLinks)) {
            return [];
        }
        $connection = $this->getConnection();
        $connection->delete($this->getMainTable(), [
            'finder_id = ?' => (int)$finder->getId(),
            'category_id IN(?)' => $deleteLinks
        ]);

        return array_column($deleteLinks, 'category_id');
    }
}
