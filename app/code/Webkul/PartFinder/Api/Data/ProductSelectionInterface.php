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

interface ProductSelectionInterface
{
    const FINDER_ID = 'finder_id';
    const ENTITY_ID = 'entity_id';
    const PRODUCT_ID = 'product_id';
    const VARIATION_KEY = 'variation_key';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getId();

    /**
     * Set entity_id
     * @param int $id
     * @return \Webkul\PartFinder\Api\Data\ProductSelectionInterface
     */
    public function setId($id);

    /**
     * Get finder_id
     * @return int|null
     */
    public function getFinderId();

    /**
     * Set finder_id
     * @param int $finderId
     * @return \Webkul\PartFinder\Api\Data\ProductSelectionInterface
     */
    public function setFinderId($finderId);

    /**
     * Get product_id
     * @return int|null
     */
    public function getProductId();

    /**
     * Set product_id
     * @param int $productId
     * @return \Webkul\PartFinder\Api\Data\ProductSelectionInterface
     */
    public function setProductId($productId);

    /**
     * Get variation_key
     * @return string|null
     */
    public function getVariationKey();

    /**
     * Set variation_key
     * @param string $key
     * @return \Webkul\PartFinder\Api\Data\ProductSelectionInterface
     */
    public function setVariationKey($key);
}
