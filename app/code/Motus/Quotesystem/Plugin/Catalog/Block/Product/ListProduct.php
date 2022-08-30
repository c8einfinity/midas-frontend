<?php
/**
 * Motus
 */

namespace Motus\Quotesystem\Plugin\Catalog\Block\Product;

class ListProduct
{

    protected $quoteHelper;

    private $_productInfo;

    /**
     * @param \Motus\Quotesystem\Helper\Data $quoteHelper
     */
    public function __construct(
        \Motus\Quotesystem\Helper\Data $quoteHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory

    ) {
        $this->quoteHelper = $quoteHelper;
        $this->repository = $productRepository;
        $this->_productFactory = $productFactory;

    }
    
    public function afterGetProductPrice(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        $result
    ) {
        $showPrice = (int)$this->quoteHelper->getConfigShowPrice();
        $modStatus = $this->quoteHelper->getModuleStatus();
        $id = $this->_productInfo->getEntityId();
        $quoteStatus = $this->repository->getById($id)->getQuoteStatus();
        $product = $this->_productFactory->create()->load($id);
        $cats = $product->getCategoryIds();

        //this is category list
        if (in_array("1025", $cats)){
            $showPrice = 1;
            $modStatus = 0;
            $quoteStatus = 0;

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
