<?php
/**
 Do you wish to enable Quote
 */

namespace Motus\Quotesystem\Controller\Rewrite\Currency;

use Magento\Store\Model\StoreManagerInterface;

class SwitchAction extends \Magento\Directory\Controller\Currency\SwitchAction
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Motus\Quotesystem\Helper\Data
     */
    protected $_helper;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cartModel;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Motus\Quotesystem\Helper\Data       $helper
     * @param \Magento\Checkout\Model\Session       $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Motus\Quotesystem\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $checkoutCartModel,
        StoreManagerInterface $storeManager
    ) {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->_cartModel = $checkoutCartModel;
        parent::__construct($context);
    }
    public function execute()
    {
        /**
 * @var \Magento\Store\Model\StoreManagerInterface $storeManager
*/
        $storeManager = $this->_storeManager;
        $currency = (string) $this->getRequest()->getParam('currency');
        if ($currency) {
            $currentCurrencyCode = $this->_helper->getCurrentCurrencyCode();
            $baseCurrency = $this->_helper->getBaseCurrencyCode();

            $storeManager->getStore()->setCurrentCurrencyCode($currency);

            $session = $this->_helper->getCheckoutSession();
            foreach ($session->getQuote()->getAllItems() as $item) {
                if ($item->getParentItemId() === null && $item->getItemId() > 0) {
                    $price = 0;
                    $quoteId = 0;
                    $quoteQty = 0;
                    $quoteCollection = $this->_helper->getMotQuoteModel()->getCollection()
                        ->addFieldToFilter("item_id", $item->getItemId());

                    if ($quoteCollection->getSize()) {
                        foreach ($quoteCollection as $quote) {
                            $price = $quote->getQuotePrice();
                            $quoteId = $quote->getEntityId();
                            $quoteQty = $quote->getQuoteQty();
                        }
                    }
                    if ($currency == $baseCurrency) {
                        $priceOne = $price;
                    } else {
                        $priceOne = $this->_helper->getmotconvertCurrency($currentCurrencyCode, $currency, $price);
                    }
                    if ($quoteId != 0) {
                        $item->setCustomPrice($priceOne);
                        $item->setOriginalCustomPrice($priceOne);
                        $item->setQty($quoteQty);
                        $item->setRowTotal($priceOne * $quoteQty);
                        $item->getProduct()->setIsSuperMode(true);
                        $item->save();
                    }
                }
            }
        }
        $this->_cartModel->save();
        $storeUrl = $storeManager->getStore()->getBaseUrl();
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($storeUrl));
    }
}
