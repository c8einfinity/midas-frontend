<?php
/**
 * Motus
 */

namespace Motus\Quotesystem\Plugin\Catalog\Block\Product\View\Options;

class AbstractOptions
{

    protected $quoteHelper;

    /**
     * @param \Motus\Quotesystem\Helper\Data $quoteHelper
     */
    public function __construct(
        \Motus\Quotesystem\Helper\Data $quoteHelper
    ) {
        $this->quoteHelper = $quoteHelper;
    }
    
    /**
     * plugin to update format price string
     *
     * @param  \Magento\Catalog\Block\Product\View\Options\AbstractOptions $subject
     * @param  [string]                                                    $result
     * @return string
     */
    public function afterGetFormatedPrice(
        \Magento\Catalog\Block\Product\View\Options\AbstractOptions $subject,
        $result
    ) {
        $showPrice = (int)$this->quoteHelper->getConfigShowPrice();
        $product = $subject->getProduct();
        $quoteStatus = $product->getQuoteStatus();
        if (!$showPrice && ($quoteStatus == 1)) {
            return '';
        }
        return $result;
    }
}
