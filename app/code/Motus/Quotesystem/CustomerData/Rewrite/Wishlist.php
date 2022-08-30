<?php
/**
 * Motus
 */

namespace Motus\Quotesystem\CustomerData\Rewrite;

class Wishlist extends \Magento\Wishlist\CustomerData\Wishlist
{
    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Wishlist\Block\Customer\Sidebar
     */
    protected $block;

    /**
     * @var \Motus\Quotesystem\Helper\Data
     */
    protected $quoteHelper;

    /**
     * @param \Magento\Wishlist\Helper\Data            $wishlistHelper
     * @param \Magento\Wishlist\Block\Customer\Sidebar $block
     * @param \Magento\Catalog\Helper\ImageFactory     $imageHelperFactory
     * @param \Magento\Framework\App\ViewInterface     $view
     * @param \Motus\Quotesystem\Helper\Data          $quoteHelper
     */
    public function __construct(
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Wishlist\Block\Customer\Sidebar $block,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Framework\App\ViewInterface $view,
        \Motus\Quotesystem\Helper\Data $quoteHelper
    ) {
        $this->wishlistHelper = $wishlistHelper;
        $this->block = $block;
        $this->quoteHelper = $quoteHelper;
        parent::__construct(
            $wishlistHelper,
            $block,
            $imageHelperFactory,
            $view
        );
    }

    /**
     * Retrieve wishlist item data
     *
     * @param  \Magento\Wishlist\Model\Item $wishlistItem
     * @return array
     */
    protected function getItemData(\Magento\Wishlist\Model\Item $wishlistItem)
    {
        $product = $wishlistItem->getProduct();
        $quotePrice = false;
        $quoteAddToCart = false;
        $product = $this->quoteHelper->getProductById($product->getId());
        $status = $product->getQuoteStatus();
        $showPrice = (int)$this->quoteHelper->getConfigShowPrice();
        $showAddToCart = (int)$this->quoteHelper->getConfigAddToCart();

        if (($status == 1) && !$showPrice) {
            $quotePrice = true;
        }
        if (($status == 1) && !$showAddToCart) {
            $quoteAddToCart = true;
        }
        return [
            'image' => $this->getImageData($product),
            'product_url' => $this->wishlistHelper->getProductUrl($wishlistItem),
            'product_name' => $product->getName(),
            'product_price' => $quotePrice ? '' : $this->block->getProductPriceHtml(
                $product,
                'wishlist_configured_price',
                \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                ['item' => $wishlistItem]
            ),
            'product_is_saleable_and_visible' => $quoteAddToCart ? !$quoteAddToCart : $product->isSaleable() &&
                                                    $product->isVisibleInSiteVisibility(),
            'product_has_required_options' => $product->getTypeInstance()->hasRequiredOptions($product),
            'add_to_cart_params' => $this->wishlistHelper->getAddToCartParams($wishlistItem, true),
            'delete_item_params' => $this->wishlistHelper->getRemoveParams($wishlistItem, true),
        ];
    }
}
