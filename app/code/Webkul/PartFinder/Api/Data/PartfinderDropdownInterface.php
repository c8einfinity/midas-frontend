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

interface PartfinderDropdownInterface
{

    const OPTION_SORTING = 'option_sorting';
    const IS_REQUIRED = 'is_required';
    const LABEL = 'label';
    const ATTRIBUTE_ID = 'attribute_id';
    const IS_MAPPED = 'is_mapped';
    const FINDER_ID = 'finder_id';
    const ENTITY_ID = 'entity_id';
    const SORT_ORDER = 'sort_order';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getId();

    /**
     * Set entity_id
     * @param string $id
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setId($id);

    /**
     * Get finder_id
     * @return string|null
     */
    public function getFinderId();

    /**
     * Set finder_id
     * @param string $finderId
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setFinderId($finderId);

    /**
     * Get label
     * @return string|null
     */
    public function getLabel();

    /**
     * Set label
     * @param string $label
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setLabel($label);

    /**
     * Get attribute_id
     * @return string|null
     */
    public function getAttributeId();

    /**
     * Set attribute_id
     * @param string $attributeId
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setAttributeId($attributeId);

    /**
     * Get is_mapped
     * @return string|null
     */
    public function getIsMapped();

    /**
     * Set is_mapped
     * @param string $isMapped
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setIsMapped($isMapped);

    /**
     * Get is_required
     * @return string|null
     */
    public function getIsRequired();

    /**
     * Set is_required
     * @param string $isRequired
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setIsRequired($isRequired);

    /**
     * Get options sort order
     * @return string|null
     */
    public function getOptionsSortOrder();

    /**
     * Set options sort order
     * @param string $sortOrder
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setOptionsSortOrder($sortOrder);

    /**
     * Get sort order
     * @return string|null
     */
    public function getSortOrder();

    /**
     * Set sort order
     * @param string $sortOrder
     * @return \Webkul\PartFinder\Api\Data\PartfinderDropdownInterface
     */
    public function setSortOrder($sortOrder);
}
