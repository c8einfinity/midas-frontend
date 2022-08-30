<?php
/**
 * Quote Conversation Model
 */

namespace Motus\Quotesystem\Model;

use Motus\Quotesystem\Api\Data\QuoteConversationInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Quoteconversation extends AbstractModel implements QuoteConversationInterface, IdentityInterface
{
   
    const CACHE_TAG = 'quotesystem_quoteconversation';

    /**
     * @var string
     */
    protected $_cacheTag = 'quotesystem_quoteconversation';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'quotesystem_quoteconversation';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Motus\Quotesystem\Model\ResourceModel\Quoteconversation ::class);
    }
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITYID);
    }
    /**
     * Set ID
     *
     * @return \Motus\Quotesystem\Model\Quotesconversation
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITYID, $id);
    }

    /**
     * Get attachments
     *
     * @return string|null
     */
    public function getAttachments()
    {
        return $this->getData(self::ATTACHMENTS);
    }
    
    /**
     * set attachments
     *
     * @param string $attachments
     *
     * @return \Motus\Quotesystem\Api\Data\QuoteInterface
     */
    public function setAttachments($attachments)
    {
        return $this->setData(self::ATTACHMENTS, $attachments);
    }
}
