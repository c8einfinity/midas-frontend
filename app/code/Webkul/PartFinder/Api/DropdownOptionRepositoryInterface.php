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

interface DropdownOptionRepositoryInterface
{

    /**
     * Save DropdownOption
     * @param \Webkul\PartFinder\Api\Data\DropdownOptionInterface $dropdownOption
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Webkul\PartFinder\Api\Data\DropdownOptionInterface $dropdownOption
    );

    /**
     * Retrieve DropdownOption
     * @param string $dropdownoptionId
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($dropdownoptionId);

    /**
     * Retrieve DropdownOption matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete DropdownOption
     * @param \Webkul\PartFinder\Api\Data\DropdownOptionInterface $dropdownOption
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Webkul\PartFinder\Api\Data\DropdownOptionInterface $dropdownOption
    );

    /**
     * Delete DropdownOption by ID
     * @param string $dropdownoptionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($dropdownoptionId);

    /**
     * get option collection by dropdown id
     *
     * @param \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface $dropdown
     * @return void
     */
    public function getDropdownOptions(\Webkul\PartFinder\Api\Data\PartfinderDropdownInterface $dropdown);
}
