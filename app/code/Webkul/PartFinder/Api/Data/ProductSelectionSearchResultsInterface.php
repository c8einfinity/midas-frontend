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

interface ProductSelectionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get ProductSelection list.
     * @return \Webkul\PartFinder\Api\Data\ProductSelectionInterface[]
     */
    public function getItems();

    /**
     * Set finder_id list.
     * @param \Webkul\PartFinder\Api\Data\ProductSelectionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
