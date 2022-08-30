<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_PartFinder
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\PartFinder\Api\Data;

interface ProfileDataSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get ProfileData list.
     * @return \Webkul\PartFinder\Api\Data\ProfileDataInterface[]
     */
    public function getItems();

    /**
     * Set entity_id list.
     * @param \Webkul\PartFinder\Api\Data\ProfileDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
