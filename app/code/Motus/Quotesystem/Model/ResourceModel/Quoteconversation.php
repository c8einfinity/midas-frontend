<?php
/**
 * Quote Quoteconversation.php
 */

namespace Motus\Quotesystem\Model\ResourceModel;

class Quoteconversation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('mot_quote_conversation', 'entity_id');
    }
}
