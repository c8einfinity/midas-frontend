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
namespace Webkul\PartFinder\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ProductSelectionRepositoryInterface
{

    /**
     * Save ProductSelection
     * @param \Webkul\PartFinder\Api\Data\ProductSelectionInterface $productSelection
     * @return \Webkul\PartFinder\Api\Data\ProductSelectionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Webkul\PartFinder\Api\Data\ProductSelectionInterface $productSelection
    );

    /**
     * Retrieve ProductSelection
     * @param string $productselectionId
     * @return \Webkul\PartFinder\Api\Data\ProductSelectionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($productselectionId);

    /**
     * Retrieve ProductSelection matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\PartFinder\Api\Data\ProductSelectionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete ProductSelection
     * @param \Webkul\PartFinder\Api\Data\ProductSelectionInterface $productSelection
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Webkul\PartFinder\Api\Data\ProductSelectionInterface $productSelection
    );

    /**
     * Delete ProductSelection by ID
     * @param string $productselectionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($productselectionId);
}
