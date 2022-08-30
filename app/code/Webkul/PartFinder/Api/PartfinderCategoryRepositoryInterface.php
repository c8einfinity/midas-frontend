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

interface PartfinderCategoryRepositoryInterface
{

    /**
     * Save PartfinderCategory
     * @param \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface $partfinderCategory
     * @return \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface $partfinderCategory
    );

    /**
     * Retrieve PartfinderCategory
     * @param string $partfindercategoryId
     * @return \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($partfindercategoryId);

    /**
     * Retrieve PartfinderCategory matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\PartFinder\Api\Data\PartfinderCategorySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete PartfinderCategory
     * @param \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface $partfinderCategory
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Webkul\PartFinder\Api\Data\PartfinderCategoryInterface $partfinderCategory
    );

    /**
     * Delete PartfinderCategory by ID
     * @param string $partfindercategoryId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($partfindercategoryId);
}
