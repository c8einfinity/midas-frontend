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

use Webkul\PartFinder\Api\Data\ProductSelectionInterface;
use Webkul\PartFinder\Model\Partfinder;

class ProductSelection extends \Magento\Framework\Model\AbstractModel implements ProductSelectionInterface
{

    protected $_eventPrefix = 'webkul_partfinder_productselection';

    protected $finder;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\PartFinder\Model\ResourceModel\ProductSelection::class);
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
     * @return \Webkul\PartFinder\Api\Data\ProductSelectionInterface
     */
    public function setProductselectionId($id)
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
     * @return \Webkul\PartFinder\Api\Data\ProductSelectionInterface
     */
    public function setFinderId($finderId)
    {
        return $this->setData(self::FINDER_ID, $finderId);
    }

    /**
     * Get product_id
     * @return string
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Set product_id
     * @param string $productId
     * @return \Webkul\PartFinder\Api\Data\ProductSelectionInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get variation_key
     * @return string|null
     */
    public function getVariationKey()
    {
        return $this->getData(self::VARIATION_KEY);
    }

    /**
     * Set variation_key
     * @param string $key
     * @return \Webkul\PartFinder\Api\Data\ProductSelectionInterface
     */
    public function setVariationKey($key)
    {
        return $this->setData(self::VARIATION_KEY, $key);
    }

    /**
     * Retrieve product instance
     *
     * @return Partfinder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * Set finder instance
     *
     * @param Partfinder $finder
     * @return $this
     */
    public function setFinder(Partfinder $finder = null)
    {
        $this->finder = $finder;
        return $this;
    }

    /**
     * @param Partfinder $finder
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getManualCollection(Partfinder $finder)
    {
        $collection = clone $this->getCollection();
        $collection->addFieldToFilter(
            'finder_id',
            $finder->getId()
        );
        return $collection;
    }
}
