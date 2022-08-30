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
namespace Webkul\PartFinder\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ProfileDataRepositoryInterface
{

    /**
     * Save ProfileData
     * @param \Webkul\PartFinder\Api\Data\ProfileDataInterface $profileData
     * @return \Webkul\PartFinder\Api\Data\ProfileDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Webkul\PartFinder\Api\Data\ProfileDataInterface $profileData
    );

    /**
     * Retrieve ProfileData
     * @param string $profiledataId
     * @return \Webkul\PartFinder\Api\Data\ProfileDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($profiledataId);

    /**
     * Retrieve ProfileData matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\PartFinder\Api\Data\ProfileDataSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete ProfileData
     * @param \Webkul\PartFinder\Api\Data\ProfileDataInterface $profileData
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Webkul\PartFinder\Api\Data\ProfileDataInterface $profileData
    );

    /**
     * Delete ProfileData by ID
     * @param string $profiledataId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($profiledataId);
}
