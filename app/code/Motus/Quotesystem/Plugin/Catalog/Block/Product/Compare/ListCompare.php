<?php
/**
 Do you wish to enable Quote on this product.
 */
namespace Motus\Quotesystem\Plugin\Catalog\Block\Product\Compare;

use Magento\Catalog\Block\Product\Compare\ListCompare as Compare;

class ListCompare
{
    /**
     * @var \Motus\Quotesystem\Helper\Data
     */
    private $_quotesystemHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Motus\Preorder\Helper\Data $preorderHelper
     */
    public function __construct(
        \Motus\Quotesystem\Helper\Data $helper
    ) {
        $this->_quotesystemHelper = $helper;
    }

    public function aroundGetProductPrice(
        Compare $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product,
        $idSuffix
    ) {
        $result = $proceed($product, $idSuffix);
        $isQuoted = $product->getQuoteStatus();
        if ($isQuoted == 1) {
            $showPrice = (int)$this->_quotesystemHelper->getConfigShowPrice();
            $button = "<div class='actions-primary quote_button'>
                            <a href='".$product->getProductUrl()."'>
                                <span title='Add Quote' class='action toquote primary'>
                                    <span>Add Quote</span>
                                </span>
                            </a>
                        </div>";
            if (!$showPrice) {
                return $button;
            }
            $result = $result.$button;
        }
        return $result;
    }
}
