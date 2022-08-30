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

interface PartfinderProductSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get PartfinderProduct list.
     * @return \Webkul\PartFinder\Api\Data\PartfinderProductInterface[]
     */
    public function getItems();

    /**
     * Set finder_id list.
     * @param \Webkul\PartFinder\Api\Data\PartfinderProductInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
