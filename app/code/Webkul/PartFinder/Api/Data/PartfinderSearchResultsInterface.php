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

interface PartfinderSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Partfinder list.
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \Webkul\PartFinder\Api\Data\PartfinderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
