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
namespace Webkul\PartFinder\Api\Data;

interface DropdownOptionInterface
{

    const ENTITY_ID = 'entity_id';
    const VALUE = 'value';
    const LABEL = 'label';
    const DROPDOWN_ID = 'dropdown_id';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getId();

    /**
     * Set entity_id
     * @param string $id
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface
     */
    public function setId($id);

    /**
     * Get dropdown_id
     * @return string|null
     */
    public function getDropdownId();

    /**
     * Set dropdown_id
     * @param string $dropdownId
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface
     */
    public function setDropdownId($dropdownId);

    /**
     * Get label
     * @return string|null
     */
    public function getLabel();

    /**
     * Set label
     * @param string $label
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface
     */
    public function setLabel($label);

    /**
     * Get value
     * @return string|null
     */
    public function getValue();

    /**
     * Set value
     * @param string $value
     * @return \Webkul\PartFinder\Api\Data\DropdownOptionInterface
     */
    public function setValue($value);
}
