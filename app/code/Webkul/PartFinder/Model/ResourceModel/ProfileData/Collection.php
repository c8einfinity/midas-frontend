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
namespace Webkul\PartFinder\Model\ResourceModel\ProfileData;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\PartFinder\Model\ProfileData::class,
            \Webkul\PartFinder\Model\ResourceModel\ProfileData::class
        );
    }
}
