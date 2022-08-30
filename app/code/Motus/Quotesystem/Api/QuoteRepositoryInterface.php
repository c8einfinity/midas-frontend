<?php
/**
 * Quote Interface


 */

namespace Motus\Quotesystem\Api;

/**
 * quote interface.
 *
 * @api
 */
interface QuoteRepositoryInterface
{
    /**
     * Create or update a quote.
     *
     * @param  \Motus\Quotesystem\Api\Data\QuoteInterface $quote
     * @return \Motus\Quotesystem\Api\Data\QuoteInterface
     */
    public function save(\Motus\Quotesystem\Api\Data\QuoteInterface $quote);

    /**
     * Get quote by quote Id
     *
     * @param  int $quoteId
     * @return \Motus\Quotesystem\Api\Data\QuoteInterface
     */
    public function getById($quoteId);

    /**
     * Delete quote.
     *
     * @param  \Motus\Quotesystem\Api\Data\QuoteInterface $quote
     * @return bool true on success
     */
    public function delete(\Motus\Quotesystem\Api\Data\QuoteInterface $quote);

    /**
     * Delete quote by ID.
     *
     * @param  int $quoteId
     * @return bool true on success
     */
    public function deleteById($quoteId);

    /**
     * get Product Name by quote ID.
     *
     * @param  int $quoteId
     * @return bool true on success
     */
    public function getProductByQuoteId($quoteId);

    /**
     * Get Id of the customer of the quote which you want to delete.
     *
     * @param  int $quoteId
     * @return int $customerId
     */
    public function getCustomerIdByQuoteId($quoteId);
}
