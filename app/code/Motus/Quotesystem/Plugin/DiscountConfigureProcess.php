<?php


namespace Motus\Quotesystem\Plugin;

/**
 * Class DiscountConfigureProcess
 *
 * Removes discount block when wallet amount product is in cart.
 */
class DiscountConfigureProcess
{
    /**
     * @var \Motus\Quotesystem\Helper\Data
     */
    private $quoteHelper;

    /**
     * @param \Motus\Quotesystem\Helper\Data $quoteHelper
     */
    public function __construct(
        \Motus\Quotesystem\Helper\Data $quoteHelper
    ) {
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * Checkout LayoutProcessor before process plugin.
     *
     * @param                                         \Magento\Checkout\Block\Checkout\LayoutProcessor $processor
     * @param                                         array                                            $jsLayout
     * @return                                        array
     */
    public function aroundProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $LayoutProcessor,
        callable $proceed,
        $jsLayout
    ) {
        $jsLayout = $proceed($jsLayout);
        if (!$this->quoteHelper->getDiscountEnable() && $this->quoteHelper->checkQuoteProductIsInCart()) {
            unset(
                $jsLayout['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']['afterMethods']['children']['discount']
            );
            unset(
                $jsLayout['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']['afterMethods']['children']['reward_amount']
            );
        }
        return $jsLayout;
    }
}
