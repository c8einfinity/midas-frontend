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

interface PartfinderDropdownSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get PartfinderDropdown list.
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface[]
     */
    public function getItems();

    /**
     * Set finder_id list.
     * @param \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
