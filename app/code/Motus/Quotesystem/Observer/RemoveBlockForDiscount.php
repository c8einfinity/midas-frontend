<?php
/**
 Do you wish to enable Quote on this product.
 */

namespace Motus\Quotesystem\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RemoveBlockForDiscount implements ObserverInterface
{
    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected $quoteHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Motus\Quotesystem\Helper\Data $quoteHelper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->quoteHelper = $quoteHelper;
    }

    public function execute(Observer $observer)
    {
        /**
 * @var \Magento\Framework\View\Layout $layout
*/
        $layout = $observer->getLayout();
        $block = $layout->getBlock('checkout.cart.coupon');

        if ($block) {
            if (!$this->quoteHelper->getDiscountEnable() && $this->quoteHelper->checkQuoteProductIsInCart()) {
                $layout->unsetElement('checkout.cart.coupon');
            }
        }
        return $this;
    }
}
