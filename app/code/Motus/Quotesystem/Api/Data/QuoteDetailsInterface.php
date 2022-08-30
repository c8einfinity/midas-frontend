<?php
/**
 * Quote Interface
 */

namespace Motus\Quotesystem\Api\Data;

interface QuoteDetailsInterface
{
    /**
     * #@+
     * Constants for keys of data array.
     */
    const ENTITYID = 'entity_id';

    const QUOTEID = 'quote_id';

    /**
     * Get entity ID
     *
     * @return int|null
     */
    public function getEntityId();
    /**
     * Set entity ID
     *
     * @param  int $id [entity id]
     * @return \Motus\Quotesystem\Api\Data\QuoteDetailsInterface
     */
    public function setEntityId($id);

    /**
     * Get entity Quote ID
     *
     * @return int|null
     */
    public function getQuoteId();
    /**
     * Set entity Quote ID
     *
     * @param  int $quoteId
     * @return \Motus\Quotesystem\Api\Data\QuoteDetailsInterface
     */
    public function setQuoteId($quoteId);
}
