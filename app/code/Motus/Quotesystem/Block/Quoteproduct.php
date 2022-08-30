<?php
/**
 * Block for add quote on a product on product page
 */

namespace Motus\Quotesystem\Block;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Catalog\Model\Product;
use Motus\Quotesystem\Helper\Data;

class Quoteproduct extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product = null;
    
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_session;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var \Motus\Quotesystem\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @var StoreManager
     */
    protected $_storeManager;

    /**
     * @var Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    protected $_stockItemRepository;

    /**
     * @param \Magento\Framework\Registry     $registry
     * @param Customer                        $customer
     * @param \Magento\Customer\Model\Session $session
     * @param Data                            $helper
     * @param array                           $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        Customer $customer,
        \Magento\Customer\Model\Session $session,
        Data $helper,
        \Magento\Directory\Model\Currency $currency,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\CatalogInventory\Model\Stock\Item $stockFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->_storeManager = $context->getStoreManager();
        $this->_currency = $currency;
        $this->_customer = $customer;
        $this->_session = $session;
        $this->_coreRegistry = $registry;
        $this->_helper = $helper;
        $this->_stockItemRepository = $stockItemRepository;
        $this->_stockFactory = $stockFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * get Product which is saved in registry
     *
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product->setStore($this->_helper->getStore());
    }

    /**
     * use to get current url.
     */
    public function getCurrentUrl()
    {
        // Give the current url of recently viewed page
        return $this->_urlBuilder->getCurrentUrl();
    }
    
    public function getIsSecure()
    {
        return $this->getRequest()->isSecure();
    }

    public function getCurrentCurrency()
    {
        $code = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        return $this->_currency->load($code)->getCurrencySymbol();
    }

    public function stockProductId()
    {
        $productId = $this->getRequest()->getParam('product_id');
        if (!$productId) {
            $productId = $this->getRequest()->getParam('id');
        }
        $stockProducts = $this->_stockFactory->load($productId, 'product_id');
        return $stockProducts;
    }

    public function getProductItemId($stockProducts)
    {
        $stockItemId = $stockProducts->getId();
        return $stockItemId;
    }
    public function getProductQty()
    {
        $stockProductsId = $this->stockProductId();
        return $this->_stockItemRepository->get($this->getProductItemId($stockProductsId))->getQty();
    }

    public function isInStock()
    {
        $stockProducts = $this->stockProductId();
        $stockItemId = $stockProducts->getIsInStock();
        return $stockItemId;
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
