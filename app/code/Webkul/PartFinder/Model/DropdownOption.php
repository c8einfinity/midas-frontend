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
namespace Webkul\PartFinder\Model;

use Webkul\PartFinder\Api\Data\DropdownOptionInterface;

class DropdownOption extends \Magento\Framework\Model\AbstractModel implements DropdownOptionInterface
{

    protected $_eventPrefix = 'webkul_partfinder_dropdownoption';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\PartFinder\Model\ResourceModel\DropdownOption::class);
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
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get entity_id
     * @return int
     */
    public function getOptionId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param int $id
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface
     */
    public function setOptionId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get dropdown_id
     * @return int
     */
    public function getDropdownId()
    {
        return $this->getData(self::DROPDOWN_ID);
    }

    /**
     * Set dropdown_id
     * @param int $dropdownId
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface
     */
    public function setDropdownId($dropdownId)
    {
        return $this->setData(self::DROPDOWN_ID, $dropdownId);
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
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * Get value
     * @return string
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * Set value
     * @param string $value
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * Set is new
     * @param string $isNew
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface
     */
    public function setIsNew($isNew)
    {
        return $this->setData('is_new', $isNew);
    }

    /**
     * Get is new
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface
     */
    public function getIsNew()
    {
        return $this->setData('is_new');
    }
}
