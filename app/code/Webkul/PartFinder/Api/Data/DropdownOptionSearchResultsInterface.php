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

interface DropdownOptionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get DropdownOption list.
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface[]
     */
    public function getItems();

    /**
     * Set dropdown_id list.
     * @param \Webkul\PartFinder\Api\Data\DropdownOptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
