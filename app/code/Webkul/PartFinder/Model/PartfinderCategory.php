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

use Webkul\PartFinder\Api\Data\PartfinderCategoryInterface;

class PartfinderCategory extends \Magento\Framework\Model\AbstractModel implements PartfinderCategoryInterface
{

    protected $_eventPrefix = 'webkul_partfinder_partfindercategory';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\PartFinder\Model\ResourceModel\PartfinderCategory::class);
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
     * @param string $id
     * @return \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get finder_id
     * @return string
     */
    public function getFinderId()
    {
        return $this->getData(self::FINDER_ID);
    }

    /**
     * Set finder_id
     * @param string $finderId
     * @return \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface
     */
    public function setFinderId($finderId)
    {
        return $this->setData(self::FINDER_ID, $finderId);
    }

    /**
     * Get category_id
     * @return string
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * Set category_id
     * @param string $categoryId
     * @return \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }
}
