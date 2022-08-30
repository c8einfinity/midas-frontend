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

interface PartfinderDropdownRepositoryInterface
{

    /**
     * Save PartfinderDropdown
     * @param \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface $partfinderDropdown
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface $partfinderDropdown
    );

    /**
     * Retrieve PartfinderDropdown
     * @param string $partfinderdropdownId
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($partfinderdropdownId);

    /**
     * Retrieve PartfinderDropdown matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete PartfinderDropdown
     * @param \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface $partfinderDropdown
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface $partfinderDropdown
    );

    /**
     * Delete PartfinderDropdown by ID
     * @param string $partfinderdropdownId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($partfinderdropdownId);

    /**
     * @param \Webkul\PartFinder\Api\Data\PartfinderInterface $fidner
     * @param bool $requiredOnly
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface[]
     */
    public function getFinderDropdowns(
        \Webkul\PartFinder\Api\Data\PartfinderInterface $fidner,
        $requiredOnly = false
    );
}
