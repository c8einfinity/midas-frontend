<?php
/**
 * Quotes Model
 */

namespace Motus\Quotesystem\Model;

use Motus\Quotesystem\Api\Data\QuoteDetailsInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class QuoteDetails extends AbstractModel implements QuoteDetailsInterface, IdentityInterface
{
    const CACHE_TAG = 'mot_quote_details';

    /**
     * @var string
     */
    protected $_cacheTag = 'mot_quote_details';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mot_quote_details';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Motus\Quotesystem\Model\ResourceModel\QuoteDetails ::class);
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
     * @return \Motus\Quotesystem\Model\QuoteDetails
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITYID, $entityId);
    }

    /**
     * Get Quote ID
     *
     * @return int|null
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTEID);
    }
    /**
     * Set Quote ID
     *
     * @return \Motus\Quotesystem\Model\Quotes
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTEID, $quoteId);
    }
}
