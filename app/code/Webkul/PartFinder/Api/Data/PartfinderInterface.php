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

interface PartfinderInterface
{
    const NAME = 'name';
    const STATUS = 'status';
    const ENTITY_ID = 'entity_id';
    const CREATED_AT = 'created_at';
    const STORE_ID = 'store_id';
    const WIDGET_NAME = 'widget_name';
    const WIDGET_CODE = 'widget_code';
    const DROPDOWN_COUNT = 'dropdown_count';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setId($entityId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setName($name);

    /**
     * Get widget name
     * @return string|null
     */
    public function getWidgetName();

    /**
     * Set widget name
     * @param string $name
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setWidgetName($name);

    /**
     * Get widget code
     * @return string|null
     */
    public function getWidgetCode();

    /**
     * Set widget code
     * @param string $code
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setWidgetCode($code);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setStatus($status);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get store_id
     * @return string|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param string $storeId
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setStoreId($storeId);

    /**
     * Get dropdown_count
     * @return string|null
     */
    public function getDropdownCount();

    /**
     * Set dropdown_count
     * @param int $count
     * @return \Webkul\PartFinder\Api\Data\PartfinderInterface
     */
    public function setDropdownCount($count);
}
