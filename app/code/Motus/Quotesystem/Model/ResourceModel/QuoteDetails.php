<?php
/**
 * Model\Quotes.php
 */

namespace Motus\Quotesystem\Model\ResourceModel;

class QuoteDetails extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('mot_quote_details', 'entity_id');
    }
}
