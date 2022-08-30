<?php
/**
 * Motus
 */

namespace Motus\Quotesystem\Pricing;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\View\Element\Template;

class Render extends \Magento\Catalog\Pricing\Render
{

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;
    public $helper;
    public $context;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Motus\Quotesystem\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $productFactory

    ) {
        $this->registry = $registry;
        $this->helper = $helper;
        $this->context = $context;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $registry);
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function _toHtml()
    {
        $product = $this->getProduct();
        $modStatus = $this->helper->getModuleStatus();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($this->helper->isShowPriceAfterLoginEnabled()) {
            $helper = $objectManager->create(\Motus\ShowPriceAfterLogin\Helper\Data::class);
        }
        if ($this->helper->isShowPriceAfterLoginEnabled() && $helper->storeAvilability()
        && $helper->isCustomerLoggedIn() || !$this->helper->isShowPriceAfterLoginEnabled() ||
        $this->helper->isShowPriceAfterLoginEnabled() && !$helper->storeAvilability()) {
            $showPrice = (int)$this->helper->getConfigShowPrice();
            $status = $product->getQuoteStatus();
            if (!($status == 1)) {
                $product = $this->helper->getProductById($product->getId());
                $status = $product->getQuoteStatus();
            }
            /**
             * @var PricingRender $priceRender
             */
            $priceRender = $this->getLayout()->getBlock($this->getPriceRender());
            $id = $product->getId();
            $product = $this->_productFactory->create()->load($id);
            $cats = $product->getCategoryIds();

            if ($priceRender instanceof PricingRender) {
                if ($product instanceof SaleableInterface) {
                    $arguments = $this->getData();
                    $arguments['render_block'] = $this;
                    $html = $priceRender->render($this->getPriceTypeCode(), $product, $arguments);
                    if (!in_array("1025", $cats)){
                        $showPrice = 1;
                        $modStatus = 0;
                        $status = 0;
                    }
                    if ($modStatus && ($status == 1) && !$showPrice) {
                        if (strlen($html) > 1) {
                          //  if (!$isInCategory = in_array("1025", $cats)){

                                return $this->helper->removePriceInfo($html);
                          //  }
                        }
                    }
                    return $html;
                }
            }
            return parent::_toHtml();
        }
    }
}
