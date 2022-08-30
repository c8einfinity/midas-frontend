<?php
/**
 * Cart product add after Observer
 */

namespace Motus\Quotesystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Motus\Quotesystem\Helper\Data;
use \Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class CheckoutCartPredispatch implements ObserverInterface
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
     * @param Data             $helper
     * @param ManagerInterface $messageManager
     */

    public function __construct(
        Data $helper,
        CheckoutSession $checkoutSession,
        ManagerInterface $messageManager
    ) {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
    }
    /**
     * cart product add after event
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->_helper;
        $session = $helper->getCheckoutSession();
        foreach ($session->getQuote()->getAllItems() as $item) {
            if ($item->getParentItemId() === null && $item->getItemId() > 0) {
                $price = 0;
                $quoteId = 0;
                $quoteQty = 0;
                $quoteCollection = $helper->getMotQuoteModel()->getCollection()
                    ->addFieldToFilter("item_id", $item->getItemId());
                $baseCurrencyCode = $helper->getBaseCurrencyCode();
                $currentCurrencyCode = $helper->getCurrentCurrencyCode();
                if ($quoteCollection->getSize()) {
                    if ($helper->checkAndUpdateForDiscount($item)) {
                        $item->setNoDiscount(1);
                    } else {
                        $item->setNoDiscount(0);
                    }
                    $item->save();
                }
            }
        }
    }
}
