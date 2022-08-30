<?php
/**
 * Quotes Repository
 */
namespace Motus\Quotesystem\Model;

use Motus\Quotesystem\Model\QuoteDetailsFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class QuoteDetailsRepository implements \Motus\Quotesystem\Api\QuoteDetailsRepositoryInterface
{
    /**
     * @var Motus\Quotesystem\Model\QuoteDetailsFactory
     */
    protected $_quoteFactory;

    /**
     * @var Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @param QuoteDetailsFactory                  $quoteFactory
     * @param \Magento\Catalog\Model\Product $product
     */
    public function __construct(
        QuoteDetailsFactory $quoteFactory,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->_quoteFactory = $quoteFactory;
        $this->_product = $product;
    }

    /**
     * Save quote.
     *
     * @param  Motus\Quotesystem\Api\Data\QuoteDetailsInterface $quote
     * @return Motus\Quotesystem\Api\Data\QuoteDetailsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Motus\Quotesystem\Api\Data\QuoteDetailsInterface $quote)
    {
        $quoteModel = null;
        if ($quote->getEntityId()) {
            $quoteModel = $this->_quoteFactory->create()->load($quote->getEntityId());
        }
        if ($quoteModel === null) {
            $quoteModel = $this->_quoteFactory->create();
            $quoteModel->addData($quote);
        } else {
            $quoteModel->addData($quote);
        }
        $quoteId = $quoteModel->save()->getEntityId();
        return $this->_quoteFactory->create()->load($quoteId);
    }
    /**
     * {@inheritdoc}
     */
    /**
     * Retrieve quote data.
     *
     * @param  int $quoteId
     * @return \Magento\Customer\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    /**
     * {@inheritdoc}
     */
    public function getById($quoteId)
    {
        if (!$quoteId) {
            throw new \Magento\Framework\Exception\InputException(__('Quote Id required'));
        }
        $quoteModel = $this->_quoteFactory->create();
        $quoteModel->load($quoteId);
        if (!$quoteModel->getEntityId()) {
            throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
        }
        return $quoteModel;
    }

    /**
     * Delete quote.
     *
     * @param  \Motus\Quotesystem\Api\Data\QuoteDetailsInterface $quote
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Motus\Quotesystem\Api\Data\QuoteDetailsInterface $quote)
    {
        try {
            $quoteId = $quote->getEntityId();
            $quote->delete();
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove quote %1', $quoteId)
            );
        }
        return true;
    }

    /**
     * Delete quote by quote ID.
     *
     * @param  int $quoteId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($quoteId)
    {
        $quoteModel = $this->getById($quoteId);
        $this->delete($quoteModel);
        return true;
    }

    /**
     * get Product Name by quote ID.
     *
     * @param  int $quoteId
     * @return bool true on success
     */
    public function getProductByQuoteId($quoteId)
    {
        $proId = $this->getById($quoteId)->getProductId();
        return $this->_product->load($proId);
    }
    /**
     * Get Id of the customer of the qoute which you want to delete.
     *
     * @param  int $quoteId
     * @return int $customerId
     */
    public function getCustomerIdByQuoteId($quoteId)
    {
        $quoteModel = $this->getById($quoteId);
        $customerId = $quoteModel->getCustomerId();
        return $customerId;
    }
}
