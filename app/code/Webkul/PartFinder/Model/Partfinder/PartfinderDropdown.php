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
namespace Webkul\PartFinder\Model\Partfinder;

use Webkul\PartFinder\Api\Data\PartfinderDropdownInterface;
use Webkul\PartFinder\Model\DropdownOption;
use Webkul\PartFinder\Model\Partfinder;

class PartfinderDropdown extends \Magento\Framework\Model\AbstractModel implements PartfinderDropdownInterface
{
    /**
     * @var string $_eventPrefix
     */
    protected $_eventPrefix = 'webkul_partfinder_partfinderdropdown';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\PartFinder\Model\ResourceModel\PartfinderDropdown::class);
    }

    /**
     * Get entity_id
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param int $id
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get finder_id
     * @return int
     */
    public function getFinderId()
    {
        return $this->getData(self::FINDER_ID);
    }

    /**
     * Set finder_id
     * @param int $finderId
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setFinderId($finderId)
    {
        return $this->setData(self::FINDER_ID, $finderId);
    }

    /**
     * Get label
     * @return string
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * Set label
     * @param string $label
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * Get attribute_id
     * @return string
     */
    public function getAttributeId()
    {
        return $this->getData(self::ATTRIBUTE_ID);
    }

    /**
     * Set attribute_id
     * @param string $attributeId
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setAttributeId($attributeId)
    {
        return $this->setData(self::ATTRIBUTE_ID, $attributeId);
    }

    /**
     * Get is_mapped
     * @return string
     */
    public function getIsMapped()
    {
        return $this->getData(self::IS_MAPPED);
    }

    /**
     * Set is_mapped
     * @param string $isMapped
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setIsMapped($isMapped)
    {
        return $this->setData(self::IS_MAPPED, $isMapped);
    }

    /**
     * Get is_required
     * @return string
     */
    public function getIsRequired()
    {
        return $this->getData(self::IS_REQUIRED);
    }

    /**
     * Set is_required
     * @param string $isRequired
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setIsRequired($isRequired)
    {
        return $this->setData(self::IS_REQUIRED, $isRequired);
    }

    /**
     * Get options sort order
     * @return string|null
     */
    public function getOptionsSortOrder()
    {
        return $this->getData(self::OPTION_SORTING);
    }

    /**
     * Set options sort order
     * @param string $sortOrder
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setOptionsSortOrder($sortOrder)
    {
        return $this->setData(self::OPTION_SORTING, $sortOrder);
    }

    /**
     * Get sort order
     * @return string|null
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * Set sort order
     * @param string $sortOrder
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Get is_new
     * @return string|null
     */
    public function getIsNew()
    {
        return $this->getData('is_new');
    }

    /**
     * Set is_new
     * @param string $isNew
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setIsNew($isNew)
    {
        return $this->setData('is_new', $isNew);
    }

    /**
     * Retrieve product instance
     *
     * @return Partfinder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * Set finder instance
     *
     * @param Partfinder $finder
     * @return $this
     */
    public function setFinder(Partfinder $finder = null)
    {
        $this->finder = $finder;
        return $this;
    }
    
    /**
     * Set dropdown options
     * @param \Webkul\PartFinder\Api\Data\DropdownOptionInterface[] $options
     * @return $this
     */
    public function setOptions(array $options = null)
    {
        $this->setData('options', $options);
        return $this;
    }

    /**
     * Get dropdown options
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface[]
     */
    public function getOptions()
    {
        return $this->getData('options');
    }

    /**
     * Add option to array of options
     *
     * @param \Webkul\PartFinder\Model\DropdownOption $option
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function addOptions(\Webkul\PartFinder\Model\DropdownOption $option)
    {
        $options = (array)$this->getData('options');
        $options[] = $option;
        $this->setData('options', $options);
        return $this;
    }

    /**
     * Save Option
     *
     * @param DropdownOption $option
     * @return void
     */
    protected function saveOption(DropdownOption $option)
    {
        $option->getLabel();
        $option->save();
    }

    /**
     * Get Dropdown Collection
     *
     * @param Partfinder $finder
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getDropdownCollection(Partfinder $finder)
    {
        $collection = clone $this->getCollection();
        $collection->addFieldToFilter(
            'finder_id',
            $finder->getId()
        )->setOrder(
            'sort_order',
            'asc'
        )->setOrder(
            'label',
            'asc'
        );
        $collection->addValuesToResult();
        return $collection;
    }
}
