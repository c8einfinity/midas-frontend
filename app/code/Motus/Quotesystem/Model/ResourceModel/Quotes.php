<?php
/**
 * Model\Quotes.php
 */

namespace Motus\Quotesystem\Model\ResourceModel;

class Quotes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('mot_quotes', 'entity_id');
    }
}
