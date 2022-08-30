<?php
/**
 * Motus
 */
namespace Motus\Quotesystem\Plugin\Product;

class ListProduct
{

    protected $quoteHelper;

    private $_productInfo;

    /**
     * @param \Motus\Quotesystem\Helper\Data $quoteHelper
     */
    public function __construct(
        \Motus\Quotesystem\Helper\Data $quoteHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->quoteHelper = $quoteHelper;
        $this->_productFactory = $productFactory;
    }

    public function afterGetProductPrice(
        \Motus\Quotesystem\Block\Product\ListProduct $subject,
        $result
    ) {
        $showPrice = (int)$this->quoteHelper->getConfigShowPrice();
        $modStatus = $this->quoteHelper->getModuleStatus();
        $quoteStatus = $this->_productInfo->getQuoteStatus();
        $product = $this->_productInfo->getId();
        $cats = $product->getCategoryIds();

        if (in_array("1025", $cats)){

            $modStatus = 0;
            $quoteStatus = 0;
            $showPrice = 1;
        }

        if ($modStatus && ($quoteStatus == 1) && !$showPrice) {

                $result = $this->quoteHelper->removePriceInfo($result);
        }
        return $result;
    }

    /**
     * beforeGetProductPrice plugin to assign the product model to a variable
     *
     * @param  \Magento\Checkout\Block\Cart\Crosssell $crosssell
     * @param  \Magento\Catalog\Model\Product         $product
     * @return \Magento\Catalog\Model\Product
     */
    public function beforeGetProductPrice(
        \Magento\Catalog\Block\Product\ListProduct $listProduct,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->_productInfo = $product ;
    }
}
