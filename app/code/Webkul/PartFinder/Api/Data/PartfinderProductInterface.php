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
namespace Webkul\PartFinder\Api\Data;

interface PartfinderProductInterface
{

    const PARTFINDERPRODUCT_ID = 'partfinderproduct_id';
    const FINDER_ID = 'finder_id';
    const PRODUCT_ID = 'product_id';

    /**
     * Get partfinderproduct_id
     * @return string|null
     */
    public function getPartfinderproductId();

    /**
     * Set partfinderproduct_id
     * @param string $partfinderproductId
     * @return \Webkul\PartFinder\Api\Data\PartfinderProductInterface
     */
    public function setPartfinderproductId($partfinderproductId);

    /**
     * Get finder_id
     * @return string|null
     */
    public function getFinderId();

    /**
     * Set finder_id
     * @param string $finderId
     * @return \Webkul\PartFinder\Api\Data\PartfinderProductInterface
     */
    public function setFinderId($finderId);

    /**
     * Get product_id
     * @return string|null
     */
    public function getProductId();

    /**
     * Set product_id
     * @param string $productId
     * @return \Webkul\PartFinder\Api\Data\PartfinderProductInterface
     */
    public function setProductId($productId);
}
