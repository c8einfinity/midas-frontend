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
namespace Webkul\PartFinder\Model;

use Magento\Framework\Model\AbstractModel;
use Webkul\PartFinder\Api\Data\ProfileDataInterface;

class ProfileData extends AbstractModel implements ProfileDataInterface
{
    /**
     * @var string $_eventPrefix
     */
    protected $_eventPrefix = 'webkul_partfinder_profiledata';

    /**
     * @var array $mappingData
     */
    protected $mappingData;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\PartFinder\Model\ResourceModel\ProfileData::class);
    }
    
    /**
     * Get entity_id
     * @return string
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Webkul\PartFinder\Api\Data\ProfileDataInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set name
     * @param string $name
     * @return \Webkul\PartFinder\Api\Data\ProfileDataInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get mapping
     * @return string
     */
    public function getMapping()
    {
        return $this->getData(self::MAPPING);
    }

    /**
     * Set mapping
     * @param string $mapping
     * @return \Webkul\PartFinder\Api\Data\ProfileDataInterface
     */
    public function setMapping($mapping)
    {
        return $this->setData(self::MAPPING, $mapping);
    }

    /**
     * Get Mapping Data
     *
     * @return array
     */
    public function getMappingData()
    {
        if (!$this->mappingData) {
            $data = $this->getMapping();
            $this->mappingData = json_decode($data, true);
        }
        return $this->mappingData;
    }
}
