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
namespace Webkul\PartFinder\Model\Partfinder\Dropdown;

use Webkul\PartFinder\Api\PartfinderDropdownRepositoryInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var Webkul\PartFinder\Api\PartfinderDropdownRepositoryInterface
     */
    protected $dropdownRepository;

    /**
     * @param PartfinderDropdownRepositoryInterface $optionRepository
     */
    public function __construct(
        PartfinderDropdownRepositoryInterface $dropdownRepository
    ) {
        $this->dropdownRepository = $dropdownRepository;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     */
    public function execute($entity, $arguments = [])
    {

        $dropdowns = $entity->getDropdowns();
        $dropdownLabel = [];
        $dropdownIds = [];
        $dropdownTitle = [];

        if ($dropdowns) {
            $dropdownIds = array_map(function ($dropdown) {
                /** @var \Webkul\PartFinder\Model\Partfinder\PartfinderDropdown $dropdown */
                return $dropdown->getId();
            }, $dropdowns);
        }

        /** @var \Webkul\PartFinder\Api\Data\PartfinderInterface $entity */
        $dropdownArray = $this->dropdownRepository->getFinderDropdowns($entity);
        if (!empty($dropdownArray)) {
            foreach ($dropdownArray as $dropdown) {
                if (!in_array($dropdown->getId(), $dropdownIds)) {
                    $this->deleteObject($this->dropdownRepository, $dropdown);
                }
            }

            $dropdownLabel = array_map(function ($dropdown) {
                /** @var \Webkul\PartFinder\Model\Partfinder\PartfinderDropdown $dropdown */
                return $dropdown->getLabel();
            }, $dropdownArray);
        }

        if ($dropdowns) {
            foreach ($dropdowns as $dropdown) {
                if (!$dropdown->getId() && in_array($dropdown->getLabel(), $dropdownLabel)) {
                    continue;
                }
                $dropdown->setFinderId($entity->getEntityId());
                $this->saveObject($this->dropdownRepository, $dropdown);
            }
        } else {
            $entity->setStatus(0);
        }

        return $entity;
    }

    /**
     * Save Object
     *
     * @param object $repository
     * @param object $object
     * @return void
     */
    public function saveObject($repository, $object)
    {
        $repository->save($object);
    }

    /**
     * Delete Object
     *
     * @param object $repository
     * @param object $object
     * @return void
     */
    public function deleteObject($repository, $object)
    {
        $repository->delete($object);
    }
}
