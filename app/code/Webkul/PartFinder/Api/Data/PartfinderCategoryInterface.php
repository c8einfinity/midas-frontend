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

interface PartfinderCategoryInterface
{

    const CATEGORY_ID = 'category_id';
    const ENTITY_ID = 'entity_id';
    const FINDER_ID = 'finder_id';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getId();

    /**
     * Set entity_id
     * @param string $id
     * @return \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface
     */
    public function setId($id);

    /**
     * Get finder_id
     * @return string|null
     */
    public function getFinderId();

    /**
     * Set finder_id
     * @param string $finderId
     * @return \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface
     */
    public function setFinderId($finderId);

    /**
     * Get category_id
     * @return string|null
     */
    public function getCategoryId();

    /**
     * Set category_id
     * @param string $categoryId
     * @return \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface
     */
    public function setCategoryId($categoryId);
}
