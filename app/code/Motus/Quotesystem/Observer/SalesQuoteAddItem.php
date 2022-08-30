<?php
/**
 Do you wish to enable Quote on this product
 */

namespace Motus\Quotesystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Motus\Quotesystem\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Motus\Quotesystem\Api\QuoteRepositoryInterface;

class SalesQuoteAddItem implements ObserverInterface
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
        $quoteItem = $observer->getQuoteItem();
        if (array_key_exists('quote_id', $params) && $params['quote_id']!='') {
            $itemProductId = $quoteItem->getProductId();
            $quoteId = $params['quote_id'];
            $quote = $this->_quoteRepository->getById($quoteId);
            $quoteProductId = $quote->getProductId();
            if ($itemProductId == $quoteProductId) {
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
                $quoteQty = $quote->getQuoteQty();
                $quoteItem->setCustomPrice($priceOne);
                $quoteItem->setOriginalCustomPrice($priceOne);
                $quoteItem->setRowTotal($priceOne * $quoteQty);
                if ($helper->checkAndUpdateForDiscount($quoteItem)) {
                    $quoteItem->setNoDiscount(1);
                } else {
                    $quoteItem->setNoDiscount(0);
                }
                $quote->setItemId($quoteItem->getItemId())->save();
            } else {
                $quoteBundleOption = $helper->convertStringAccToVersion(
                    $quote->getBundleOption(),
                    'decode'
                );
                $this->addQuoteItem($quoteBundleOption, $quote, $currentCurrencyCode, $quoteItem);
            }
        }
    }

    public function addQuoteItem($quoteBundleOption, $quote, $currentCurrencyCode, $quoteItem)
    {
        $itemProductId = $quoteItem->getProductId();
        if (!empty($quoteBundleOption)) {
            $bundleProductConfiguredPrice = $quote->getProductPrice();
            foreach ($quoteBundleOption['bundle_option'] as $optionId => $optionValue) {
                if ($quoteBundleOption['bundle_option_product'][$optionId] == $itemProductId) {
                    $currentOptionProductPrice = $quoteBundleOption['bundle_option_price'][$optionId];
                    $currentTotalPrice = $currentOptionProductPrice;
                    $calculatePercent = ($currentTotalPrice * 100)/$bundleProductConfiguredPrice;
                    $price = (($quote->getQuotePrice() * $calculatePercent)/100)/
                    $quoteBundleOption['bundle_option_qty'][$optionId];
                    $quoteCurrency = $quote->getQuoteCurrency();
                    if ($quoteCurrency != $currentCurrencyCode) {
                        $priceOne = $this->_helper->getmotconvertCurrency(
                            $quoteCurrency,
                            $currentCurrencyCode,
                            $price
                        );
                    } else {
                        $priceOne = $price;
                    }
                    $quoteItem->setCustomPrice($priceOne);
                    $quoteItem->setOriginalCustomPrice($priceOne);
                    $quoteItem->setRowTotal($priceOne * $quoteBundleOption['bundle_option_qty'][$optionId]);
                    if ($this->_helper->checkAndUpdateForDiscount($quoteItem)) {
                        $quoteItem->setNoDiscount(1);
                    } else {
                        $quoteItem->setNoDiscount(0);
                    }
                    $quoteItem->getProduct()->setIsSuperMode(true);
                }
            }
        }
    }
}
