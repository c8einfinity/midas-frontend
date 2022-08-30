<?php
/**
 * Quote Interface
 */

namespace Motus\Quotesystem\Api\Data;

interface QuoteInterface
{
    /**
* #@+
     * Constants for keys of data array.
     */
    const ENTITYID = 'entity_id';
    /**
* #@-
*/

    const ATTACHMENTS = 'attachments';

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
     * @return \Motus\Quotesystem\Api\Data\QuoteInterface
     */
    public function setEntityId($id);

    /**
     * Get attachments
     *
     * @return string|null
     */
    public function getAttachments();
    
    /**
     * set attachments
     *
     * @param string $attachments
     *
     * @return \Motus\Quotesystem\Api\Data\QuoteInterface
     */
    public function setAttachments($attachments);
}
