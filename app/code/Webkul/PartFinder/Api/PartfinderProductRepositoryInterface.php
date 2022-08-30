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

interface PartfinderProductRepositoryInterface
{

    /**
     * Save PartfinderProduct
     * @param \Webkul\PartFinder\Api\Data\PartfinderProductInterface $partfinderProduct
     * @return \Webkul\PartFinder\Api\Data\PartfinderProductInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Webkul\PartFinder\Api\Data\PartfinderProductInterface $partfinderProduct
    );

    /**
     * Retrieve PartfinderProduct
     * @param string $partfinderproductId
     * @return \Webkul\PartFinder\Api\Data\PartfinderProductInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($partfinderproductId);

    /**
     * Retrieve PartfinderProduct matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\PartFinder\Api\Data\PartfinderProductSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete PartfinderProduct
     * @param \Webkul\PartFinder\Api\Data\PartfinderProductInterface $partfinderProduct
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Webkul\PartFinder\Api\Data\PartfinderProductInterface $partfinderProduct
    );

    /**
     * Delete PartfinderProduct by ID
     * @param string $partfinderproductId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($partfinderproductId);
}
