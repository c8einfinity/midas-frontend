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

interface PartfinderCategorySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get PartfinderCategory list.
     * @return \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface[]
     */
    public function getItems();

    /**
     * Set finder_id list.
     * @param \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
