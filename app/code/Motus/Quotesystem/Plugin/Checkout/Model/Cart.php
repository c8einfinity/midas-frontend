<?php
/**
 * Motus
 */
namespace Motus\Quotesystem\Plugin\Checkout\Model;

use Magento\Framework\Exception\LocalizedException;

class Cart
{
    protected $quoteHelper;

    public function __construct(
        \Motus\Quotesystem\Helper\Data $quoteHelper
    ) {
        $this->quoteHelper = $quoteHelper;
    }

    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        $quoteStatus = $productInfo->getQuoteStatus();
        $cats = $productInfo->getCategoryIds();
        $allowAddToCart = (int)$this->quoteHelper->getConfigAddToCart();
        if (in_array("1025", $cats)){
            $allowAddToCart = 1;
            $quoteStatus = 0;
        }
        if (($quoteStatus == 1) && !$allowAddToCart) {
                if (array_key_exists('quote_id', $requestInfo)) {
                    return [$productInfo, $requestInfo];
                }
                throw new LocalizedException(__('Add to cart for this product is not allowed'));

        }
        return [$productInfo, $requestInfo];
    }
}
