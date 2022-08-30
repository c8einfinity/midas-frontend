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
use Webkul\PartFinder\Api\Data\PartfinderProductInterface;

class PartfinderProduct extends AbstractModel implements PartfinderProductInterface
{
    /**
     * @var string $_eventPrefix
     */
    protected $_eventPrefix = 'webkul_partfinder_partfinderproduct';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\PartFinder\Model\ResourceModel\PartfinderProduct::class);
    }

    /**
     * Get partfinderproduct_id
     * @return string
     */
    public function getPartfinderproductId()
    {
        return $this->getData(self::PARTFINDERPRODUCT_ID);
    }

    /**
     * Set partfinderproduct_id
     * @param string $partfinderproductId
     * @return \Webkul\PartFinder\Api\Data\PartfinderProductInterface
     */
    public function setPartfinderproductId($partfinderproductId)
    {
        return $this->setData(self::PARTFINDERPRODUCT_ID, $partfinderproductId);
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
     * @return \Webkul\PartFinder\Api\Data\PartfinderProductInterface
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
     * @return \Webkul\PartFinder\Api\Data\PartfinderProductInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }
}
