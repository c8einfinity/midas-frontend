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
namespace Webkul\PartFinder\Api\Data;

interface ProfileDataInterface
{
    const ENTITY_ID = 'entity_id';
    const MAPPING = 'mapping';
    const NAME = 'name';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Webkul\PartFinder\Api\Data\ProfileDataInterface
     */
    public function setEntityId($entityId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Webkul\PartFinder\Api\Data\ProfileDataInterface
     */
    public function setName($name);

    /**
     * Get mapping
     * @return string|null
     */
    public function getMapping();

    /**
     * Set mapping
     * @param string $mapping
     * @return \Webkul\PartFinder\Api\Data\ProfileDataInterface
     */
    public function setMapping($mapping);
}
