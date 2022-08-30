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
namespace Webkul\PartFinder\Model\ResourceModel\PartfinderDropdown;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Webkul\PartFinder\Api\DropdownOptionRepositoryInterface as OptionRepository;

/**
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var \Webkul\PartFinder\Api\DropdownOptionRepositoryInterface as OptionRepository
     */
    protected $optionRepository;

    /**
     * @param OptionRepository $optionRepository
     */
    public function __construct(
        OptionRepository $optionRepository
    ) {
        $this->optionRepository = $optionRepository;
    }

    /**
     * Execute function
     *
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     */
    public function execute($entity, $arguments = [])
    {
        $options = $entity->getOptions();
        
        $optionIds = [];

        if ($options) {
            $optionIds = array_map(function ($option) {
                /** @var \Webkul\PartFinder\Model\Partfinder\DropdownOption $option */
                return $option->getId();
            }, $options);
        }
        
        /** @var \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface $entity */
        foreach ($this->optionRepository->getDropdownOptions($entity) as $option) {
            if (!in_array($option->getId(), $optionIds)) {
                $this->deleteObject($this->optionRepository, $option);
            }
        }
        if ($options) {
            foreach ($options as $option) {
                $option->setDropdownId((int) $entity->getId());
                $this->saveObject($this->optionRepository, $option);
            }
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
