<?php
/**
 Do you wish to enable Quote on this product
 */

namespace Motus\Quotesystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Motus\Quotesystem\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Motus\Quotesystem\Api\QuoteRepositoryInterface;

class CheckoutCartSaveafter implements ObserverInterface
{
    /**
     * @var \Motus\Quotesystem\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @var Motus\Quotesystem\Api\QuoteRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @param Data                                $helper
     * @param ManagerInterface                    $messageManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param QuoteRepositoryInterface            $quoteRepository
     */
    public function __construct(
        Data $helper,
        ManagerInterface $messageManager,
        \Magento\Framework\App\Request\Http $request,
        QuoteRepositoryInterface $quoteRepository
    ) {
        $this->_helper = $helper;
        $this->_messageManager = $messageManager;
        $this->_request = $request;
        $this->_quoteRepository = $quoteRepository;
    }

    /**
     * quote Item qty Set after
     *
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observerData = $observer->getData();
        $params = $this->_request->getParams();
        $helper = $this->_helper;
        $session = $helper->getCheckoutSession();
        $quoteId = '';
        $baseCurrencyCode = $helper->getBaseCurrencyCode();
        $currentCurrencyCode = $helper->getCurrentCurrencyCode();
        if (array_key_exists('quote_id', $params) && $params['quote_id']!='') {
            $quoteId = $params['quote_id'];
            $quote = $this->_quoteRepository->getById($quoteId);
            $lastItemId = 0;
            foreach ($session->getQuote()->getAllItems() as $item) {
                if ($item->getParentItemId() === null && $item->getItemId() > 0) {
                    $lastItemId = $item->getItemId();
                }
            }
            if ($lastItemId!=0) {
                foreach ($session->getQuote()->getAllItems() as $item) {
                    if ($item->getItemId() == $lastItemId) {
                        $price = $quote->getQuotePrice();
                        $quoteCurrency = $quote->getQuoteCurrency();
                        if ($quoteCurrency != $currentCurrencyCode) {
                            $priceOne = $helper->getmotconvertCurrency(
                                $quoteCurrency,
                                $currentCurrencyCode,
                                $price
                            );
                        } else {
                            $priceOne = $price;
                        }
                        $item->setCustomPrice($priceOne);
                        $item->setQty($quote->getQuoteQty());
                        $item->setOriginalCustomPrice($priceOne);
                        if ($helper->checkAndUpdateForDiscount($item)) {
                            $item->setNoDiscount(1);
                        } else {
                            $item->setNoDiscount(0);
                        }
                        $item->save();
                        $quote->setItemId($item->getItemId())->save();
                    }
                }
            }
        } else {
            foreach ($session->getQuote()->getAllItems() as $item) {
                $quoteModel = $helper->getMotQuoteModel()->getCollection()
                    ->addFieldToFilter('item_id', $item->getItemId())
                    ->addFieldToFilter('product_id', $item->getProductId());
                if ($quoteModel->getSize()) {
                    foreach ($quoteModel as $quoteData) {
                        $quote = $this->_quoteRepository->getById($quoteData->getEntityId());
                    }
                    $price = $quote->getQuotePrice();
                    $quoteCurrency = $quote->getQuoteCurrency();
                    if ($quoteCurrency != $currentCurrencyCode) {
                        $priceOne = $helper->getmotconvertCurrency(
                            $quoteCurrency,
                            $currentCurrencyCode,
                            $price
                        );
                    } else {
                        $priceOne = $price;
                    }
                    $item->setCustomPrice($priceOne);
                    $item->setQty($quote->getQuoteQty());
                    $item->setOriginalCustomPrice($priceOne);
                    if ($helper->checkAndUpdateForDiscount($item)) {
                        $item->setNoDiscount(1);
                    } else {
                        $item->setNoDiscount(0);
                    }
                    $item->save();
                    $item->getProduct()->setIsSuperMode(true);
                }
            }
        }
    }
}
