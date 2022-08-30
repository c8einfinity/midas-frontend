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

interface PartfinderRepositoryInterface
{

    /**
     * Save Partfinder
     * @param \Webkul\PartFinder\Api\Data\PartfinderInterface $partfinder
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Webkul\PartFinder\Api\Data\PartfinderInterface $partfinder
    );

    /**
     * Retrieve Partfinder
     * @param string $partfinderId
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($partfinderId);

    /**
     * Retrieve Partfinder matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\PartFinder\Api\Data\PartfinderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Partfinder
     * @param \Webkul\PartFinder\Api\Data\PartfinderInterface $partfinder
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Webkul\PartFinder\Api\Data\PartfinderInterface $partfinder
    );

    /**
     * Delete Partfinder by ID
     * @param string $partfinderId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($partfinderId);
}
