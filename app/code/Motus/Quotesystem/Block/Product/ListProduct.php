<?php
/**
 * Motus
 */

namespace Motus\Quotesystem\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Url\Helper\Data as urlData;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Block\Product\Context;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{

    /**
     * @param \Motus\Quotesystem\Helper\Data                               $helper
     * @param array                                                         $data
     */
    public function __construct(
        \Motus\Quotesystem\Helper\Data $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        urlData $urlHelper,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }


    /**
     * @param Product $product
     * @return string
     */
    public function getProductPrice(Product $product)
    {
        $priceRender = $this->getPriceRender();
        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => Render::ZONE_ITEM_LIST,
                    'list_category_page' => true
                ]
            );
        }
        return $price;
    }

    /**
     * Get Helper
     *
     * @return Motus\QuoteSystem\Helper\Data
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Get Json Helper
     *
     * @return Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
