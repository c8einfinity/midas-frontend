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

class ProductSelection extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('partfinder_product_selection', 'entity_id');
    }

    /**
     * Retrieve finder product selection ids
     *
     * @param array $selectionData
     * @return array
     */
    public function getSelectionIds(array $selectionData = [])
    {
        $connection = $this->getConnection();

        $select = $connection->select();
        $select->from($this->getMainTable(), ['entity_id']);
        $select->where(
            'finder_id = ?',
            (int)$selectionData['finder_id']
        );
        $select->where('product_id = ?', (int)$selectionData['product_id']);
 
        $result = $connection->fetchAll($select);

        return $result;
    }

    /**
     * Retrieve finder product links by PartfinderInterface and product identifiers
     *
     * @param PartfinderInterface $finder
     * @param array $productIds
     * @return array
     */
    public function getProductLinks(PartfinderInterface $finder, array $productIds = [])
    {
        
        $connection = $this->getConnection();

        $select = $connection->select();
        $select->from($this->getMainTable(), ['entity_id']);
        $select->where('finder_id = ?', (int)$finder->getId());

        if (!empty($categoryIds)) {
            $select->where('product_id IN(?)', $productIds);
        }

        $result = $connection->fetchAll($select);
        return $result;
    }

    /**
     * @param PartfinderInterface $finder
     * @param array $deleteLinks
     * @return array
     */
    public function deleteProductLinks(PartfinderInterface $finder, array $deleteLinks)
    {
        if (empty($deleteLinks)) {
            return [];
        }
        $connection = $this->getConnection();
        $connection->delete($this->getMainTable(), [
            'finder_id = ?' => (int)$finder->getId(),
            'product_id IN(?)' => $deleteLinks
        ]);

        return array_column($deleteLinks, 'product_id');
    }

    /**
     * Delete selection by finder id
     *
     * @param mixed $finderId
     * @return void
     */
    public function deleteByFinderId($finderId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            $this->getIdFieldName()
        )->where(
            'finder_id = ?',
            $finderId
        );
        $this->getConnection()->query($this->getConnection()->deleteFromSelect($select, $this->getMainTable()));
    }
}
