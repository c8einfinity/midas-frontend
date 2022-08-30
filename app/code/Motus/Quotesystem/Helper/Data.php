<?php
/**
 * Quote Helper Data.php
 */

namespace Motus\Quotesystem\Helper;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Customer;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleList;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as   QuoteFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const BASE_QTY = 1;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Magento\Customer\Model\Customer
     */
    protected $_customerModel;

    /**
     * @var Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $_attribute;

    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelperData;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * @var Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_typeConfigurable;

    /**
     * @var Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @var Magento\Directory\Helper\Data
     */
    protected $_magentoDirectoryHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var Motus\Quotesystem\Model\QuotesFactory
     */
    protected $_quotesFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetaData;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var ModuleList
     */
    private $moduleList;

    /**
     * @var TimezoneInterface
     */
    private $_timezoneInterface;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $_localeCurrency;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $catalogProductHelper;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    protected $priceCurrency;

    protected $productRepository;

    protected $_registry;
    
    /**
     * @param \Magento\Framework\App\Helper\Context                $context
     * @param StoreManagerInterface                                $storeManager
     * @param Customer                                             $customerModel
     * @param ProductFactory                                       $productFactory
     * @param Attribute                                            $attribute
     * @param \Magento\Framework\Pricing\Helper\Data               $pricingHelper
     * @param \Magento\Eav\Model\Config                            $eavConfig
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param Configurable                                         $typeConfigurable
     * @param \Magento\Directory\Model\Currency                    $currency
     * @param \Magento\Directory\Helper\Data                       $helperDirectory
     * @param \Magento\Checkout\Model\Session                      $checkoutSession
     * @param \Motus\Quotesystem\Model\QuotesFactory              $quotesFactory
     * @param ProductMetadataInterface                             $productMetaData
     * @param \Magento\Framework\App\Request\Http                  $request
     * @param ModuleList                                           $moduleList
     * @param TimezoneInterface                                    $timezoneInterface
     * @param \Magento\Framework\Locale\CurrencyInterface          $localeCurrency
     * @param \Magento\Catalog\Helper\Product                      $catalogProductHelper
     * @param \Magento\Customer\Model\Session                      $customerSession
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface    $priceCurrency
     * @param \Magento\Catalog\Model\ProductRepository             $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface $storeManager,
        Customer $customerModel,
        ProductFactory $productFactory,
        Attribute $attribute,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        Configurable $typeConfigurable,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Directory\Helper\Data $helperDirectory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Motus\Quotesystem\Model\QuotesFactory $quotesFactory,
        ProductMetadataInterface $productMetaData,
        \Magento\Framework\App\Request\Http $request,
        ModuleList $moduleList,
        TimezoneInterface $timezoneInterface,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Quote\Model\QuoteFactory $quote,
        QuoteFactory $quoteFactory,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItem,
        \Magento\Catalog\Block\Product\ListProduct $productList,
        \Magento\Framework\Serialize\SerializerInterface $serializeInterface
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_customerModel = $customerModel;
        $this->_productFactory = $productFactory;
        $this->_attribute = $attribute;
        $this->_pricingHelperData = $pricingHelper;
        $this->_eavConfig = $eavConfig;
        $this->_stockRegistry = $stockRegistry;
        $this->_typeConfigurable = $typeConfigurable;
        $this->_currency = $currency;
        $this->_magentoDirectoryHelper = $helperDirectory;
        $this->_checkoutSession = $checkoutSession;
        $this->_quotesFactory = $quotesFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->moduleManager = $context->getModuleManager();
        $this->productMetaData = $productMetaData;
        $this->_request = $request;
        $this->moduleList = $moduleList;
        $this->_timezoneInterface = $timezoneInterface;
        $this->_localeCurrency = $localeCurrency;
        $this->catalogProductHelper = $catalogProductHelper;
        $this->customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
        $this->productRepository = $productRepository;
        $this->_registry = $registry;
        $this->quote = $quote;
        $this->quoteFactory = $quoteFactory;
        $this->quoteItem = $quoteItem;
        $this->productList = $productList;
        $this->serializeInterface = $serializeInterface;
    }

    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * get email id from admin system config
     *
     * @return string
     */
    public function getDefaultTransEmailId()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get customer data by customer id
     *
     * @param  integer
     * @return object
     */
    public function getCustomerData($customerId)
    {
        return $this->_customerModel->load($customerId);
    }

    /**
     * get Product data by product id
     *
     * @param  int $productId
     * @return Magento\Catalog\Model\Product
     */
    public function getProduct($productId)
    {
        return $this->_productFactory->create()->load($productId);
    }

    /**
     * use to get price and options of a product which are used when quote is submitted.
     *
     * @param Magento\catalog\Model\Product
     * @param Motus\Quotesystem\Model\Quotes
     *
     * @return string
     */
    public function getOptionNPrice($product, $quote)
    {
        $optionString = '';
        $finalPrice = 0;
        if ($product->getTypeId() == 'bundle') {
            list($returnOptionString, $returnFinalPrice) = $this->getBundleProductData($product, $quote);
            $optionString .= $returnOptionString;
            $finalPrice += $returnFinalPrice;
        } else {
            $finalPrice = $quote->getProductPrice();
            if ($product->getTypeId() == 'configurable') {
                list($returnOptionString, $returnFinalPrice) = $this->getConfigurableProductData(
                    $product,
                    $quote
                );
                $optionString .= $returnOptionString;
                $finalPrice += $returnFinalPrice;
            }
            list($returnOptionString, $returnFinalPrice) = $this->getCustomOptionData($product, $quote);
            $optionString .= $returnOptionString;
            $finalPrice += $returnFinalPrice;
            $links = $this->convertStringAccToVersion($quote->getLinks(), 'decode');
            if (isset($links[0]) && $links !="null" && is_array($links)) {
                $optionString .= "<dt><b>".__('Links').'</b></dt>';
                $optionString = getOptionString($links, $product, $optionString);
            }
        }
        return $optionString.'~|~'.$finalPrice;
    }

    public function getOptionString($links, $product, $optionString)
    {
        foreach ($links as $link) {
            $productlinks = $product->getTypeInstance()->getLinks($product);
            if (is_array($productlinks)) {
                foreach ($productlinks as $productlink) {
                    if ($productlink->getLinkId() == $link) {
                        $optionString .= "<dd>".$productlink->getTitle().'</dd>';
                    }
                }
            }
        }
        return $optionString;
    }

    public function getBundleProductData($product, $quote)
    {
        $optionString = '';
        $finalPrice = 0;
        $bundleOptions = $this->convertStringAccToVersion($quote->getBundleOption(), 'decode');
        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        $optionsCollection = $product->getTypeInstance(true)
            ->getOptionsCollection($product);
        foreach ($optionsCollection as $options) {
            $optionId = $options->getOptionId();
            if (array_key_exists($optionId, $bundleOptions['bundle_option'])
                && $valueId = $bundleOptions['bundle_option'][$optionId]
            ) {
                $optionString .= "<dt><b>"." ".
                    $options->getDefaultTitle().
                '</b></dt>';
                $optArray = [];
                $finalPrice += $this->getBundleData($selectionCollection, $valueId, $bundleOptions, $optionId);
                
                $optionString .= "<dd>"." ".
                    implode('<br>', $optArray).'</dd>';
            }
        }
        return [$optionString, $finalPrice];
    }

    public function getBundleData($selectionCollection, $valueId, $bundleOptions, $optionId)
    {
        $finalPrice = 0;
        foreach ($selectionCollection as $proselection) {
            if (is_array($valueId)) {
                if (in_array($proselection->getSelectionId(), $valueId, $optionId)) {
                    $optArray[] = '1 x '.
                        $proselection->getName().
                        ' '.
                        $this->getformattedPrice(
                            $proselection->getPrice(),
                            true,
                            false
                        );
                    $finalPrice += $proselection->getPrice();
                }
            } else {
                if ($proselection->getSelectionId() == $valueId) {
                    if (array_key_exists($optionId, $bundleOptions['bundle_option_qty'])) {
                        $bundleOptionData = $bundleOptions['bundle_option_qty'][$optionId];
                    } else {
                        $bundleOptionData = $proselection->getSelectionQty();
                    }
                    $optArray[] = $bundleOptionData.
                        ' x '.
                        $proselection->getName().
                        ' '.
                        '<br>'.
                        $this->getformattedPrice(
                            $proselection->getPrice(),
                            true,
                            false
                        );
                    $finalPrice += (
                        $bundleOptionData * $proselection->getPrice()
                        );
                }
            }
        }
        return $finalPrice;
    }
    public function getConfigurableProductData($product, $quote)
    {
        $optionString = '';
        $finalPrice = 0;
        $configurableOptions = $this->convertStringAccToVersion($quote->getSuperAttribute(), 'decode');

        $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);

        foreach ($attributes as $attribute) {
            $attributeModel = $this->_eavConfig->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $attribute['attribute_id']
            );
            $attributeCode = $attributeModel->getAttributeCode();
            $attributeArray[$attribute['attribute_id']] = $attributeCode;
        }
        $productPrice = 0;
        $productIdArray = [];
        $productWithAttributes = $product->getTypeInstance(true)->getUsedProducts($product);
        foreach ($productWithAttributes as $usedProduct) {
            $flag = 0;
            foreach ($attributeArray as $attrId => $attributeCode) {
                if (array_key_exists($attributeCode, $usedProduct->getData())) {
                    if (in_array($usedProduct[$attributeCode], $configurableOptions)) {
                        $productIdArray[] = $usedProduct->getEntityId();
                    }
                }
            }
        }
        $counts = array_count_values($productIdArray);
        arsort($counts);
        $configurableProductId = key($counts);
        $configurableProduct = $this->getProduct($configurableProductId);
        $finalPrice += $configurableProduct->getPrice();
        foreach ($configurableOptions as $attrId => $configurableOption) {
                $attr = $this->_attribute->load($attrId);
                $label = $attr->getSource()->getOptionText($configurableOption);
                $optionString .= "<dt ><b>".
                    $attr->getFrontendLabel().":".
                    '</b></dt>';
                $optionString .= "<dd >".
                    $label.
                    '</dd>';
        }
        return [$optionString, $finalPrice];
    }

    public function getOptionStringOfOptions($proOption, $option)
    {
        $finalPrice = 0;
        $optionString = '';
        $optionString .= "<dt><b>".
            $proOption['default_title'].
            '</b></dt>';
        if (in_array($proOption->getType(), ['area', 'field'])) {
            $finalPrice += $proOption->getPrice();
            $optionString .= "<dd>".
                $option.
                '</dd>';
        } elseif (in_array($proOption->getType(), ['drop_down', 'radio'])) {
            list($optionStringUpdated, $finalPrice) = $this->byDropDownAndRadio($proOption, $option);
            $optionString .= $optionStringUpdated;
        } elseif (in_array($proOption->getType(), ['multiple', 'checkbox'])) {
            $optionValues = $proOption->getValues();
            $displayableOptions = [];
            list($finalPrice, $displayableOptions) = $this->byMultipleAndcheckbox($optionValues, $option);
            $optionString .= "<dd>".
                implode(', ', $displayableOptions).
                '</dd>';
        } elseif ($proOption->getType() == 'date_time') {
            $finalPrice += $proOption->getPrice();
            $dateTime = $option['month'].
                '/'.$option['day'].
                '/'.$option['year'].
                ' '.date(
                    'H:i',
                    strtotime(
                        $option['hour'].':'.$option['minute']
                    )
                ).' '.
                strtoupper($option['day_part']);
            $optionString .= "<dd>".$dateTime.'</dd>';
        } elseif ($proOption->getType() == 'date') {
            $finalPrice += $proOption->getPrice();
            $dateTime = $option['month'].
                '/'.$option['day'].
                '/'.$option['year'];
            $optionString .= "<dd>".$dateTime.'</dd>';
        } elseif ($proOption->getType() == 'time') {
            $finalPrice += $proOption->getPrice();
            $dateTime = date(
                'H:i',
                strtotime($option['hour'].':'.$option['minute'])
            ).' '.strtoupper($option['day_part']);
            $optionString .= "<dd>".$dateTime.'</dd>';
        }
        return [$optionString,$finalPrice];
    }

    protected function byDropDownAndRadio($proOption, $option)
    {
        $finalPrice = 0;
        $optionString = '';
        $optionValues = $proOption->getValues();
        foreach ($optionValues as $optVal) {
            if ($option == $optVal->getOptionTypeId()) {
                $finalPrice += $optVal->getPrice();
                $optionString .= "<dd>".
                    $optVal->getDefaultTitle().
                    '</dd>';
            }
        }
        return [$optionString,$finalPrice];
    }

    protected function byMultipleAndcheckbox($optionValues, $option)
    {
        $finalPrice = 0;
        $displayableOptions = [];
        foreach ($optionValues as $optVal) {
            if (in_array($optVal->getOptionTypeId(), $option)) {
                $finalPrice += $optVal->getPrice();
                $displayableOptions[] = $optVal->getDefaultTitle();
            }
        }
        return [$finalPrice, $displayableOptions];
    }

    public function getCustomOptionData($product, $quote)
    {
        $optionString = '';
        $finalPrice = 0;
        if (!$product->getSku()) {
            return [$optionString, $finalPrice];
        }
        $options = $this->convertStringAccToVersion($quote->getProductOption(), 'decode');
        $productOptions = $product->getOptions();
        if (is_array($options)) {
            foreach ($options as $key => $option) {
                if (!is_array($option)) {
                    $option = explode(" ", $option);
                }
                foreach ($productOptions as $proOption) {
                    if ($proOption->getOptionId() == $key) {
                        list(
                            $returnOptionString,
                            $returnFinalPrice
                        ) = $this->getOptionStringOfOptions($proOption, $option);
                        $optionString .= $returnOptionString;
                        $finalPrice += $returnFinalPrice;
                    }
                }
            }
        }
        return [$optionString, $finalPrice];
    }

    protected function optionString($options, $optionString, $counter, $productOptions)
    {
        foreach ($options as $key => $option) {
            if ($counter != 0) {
                foreach ($productOptions as $proOption) {
                    foreach ($option as $key => $value) {
                        $idArray = explode('_', $key);
                        if (in_array($proOption->getOptionId(), $idArray)) {
                            $optionString .= "<dd>".$value['name'].'</dd>';
                        }
                    }
                }
            }
            ++$counter;
        }
        return $optionString;
    }

    /**
     * get price in current currency format
     *
     * @param  float $price
     * @return string
     */
    public function getformattedPrice($price)
    {
        return $this->_pricingHelperData
            ->currency($price, true, false);
    }

    // return currency currency code
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    // get base currency code
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    // get all allowed currency in system config
    public function getConfigAllowCurrencies()
    {
        return $this->_currency->getConfigAllowCurrencies();
    }

    /**
     * Retrieve currency rates to other currencies.
     *
     * @param string     $currency
     * @param array|null $toCurrencies
     *
     * @return array
     */
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        // give the currency rate
        return $this->_currency->getCurrencyRates($currency, $toCurrencies);
    }

    // convert amount according to currenct currency
    public function convertCurrency($amount, $from, $to)
    {
        $finalAmount = $this->_magentoDirectoryHelper
            ->currencyConvert($amount, $from, $to);

        return $finalAmount;
    }

    // convert currency amount
    public function getmotconvertCurrency($fromCurrency, $toCurrency, $amount)
    {
        $currentCurrencyCode = $this->getCurrentCurrencyCode();
        $baseCurrencyCode = $this->getBaseCurrencyCode();
        $allowedCurrencies = $this->getConfigAllowCurrencies();
        $rates = $this->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if (empty($rates[$fromCurrency])) {
            $rates[$fromCurrency] = 1;
        }

        if ($baseCurrencyCode == $toCurrency) {
            $currencyAmount = $amount/$rates[$fromCurrency];
        } else {
            $amount = $amount/$rates[$fromCurrency];
            $currencyAmount = $this->convertCurrency($amount, $baseCurrencyCode, $toCurrency);
        }
        return $currencyAmount;
    }

    /**
     * get checkout session
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * loads quotes model
     */
    public function getMotQuoteModel()
    {
        return $this->_quotesFactory->create();
    }

    public function getQuoteDataInRegistry()
    {
        return $this->_registry->registry("quoteitems");
    }

    /**
     * function to get quoteproduct info
     *
     * @param  [collection] $_productCollection
     * @return array
     */
    public function getQuotedProductInfo($_productCollection)
    {
        $auctionModuleEnabledOrNot = $this->checkModuleIsEnabledOrNot('Motus_Auction');
        $quoteProductsInfo = [];
        if ($_productCollection->getSize() == 0) {
            return [];
        }
        foreach ($_productCollection as $product) {
            $productData = $this->getProduct($product->getId());
            if ($productData->getQuoteStatus() == 1) {
                $auctionCheck = 1;
                if ($auctionModuleEnabledOrNot) {
                    $auctionValues = $productData->getAuctionType();
                    $auctionOpt = explode(',', $auctionValues);
                    if (in_array(2, $auctionOpt)) {
                        $auctionCheck = 0;
                    }
                }
                if ($auctionCheck) {
                    $productUrl = $productData->getUrlModel()->getUrl($productData, ['_ignore_category' => true]);
                    
                    if (!$productData->getTypeInstance()->isPossibleBuyFromList($productData)) {
                        $quoteProductsInfo[$productData->getId()]['url'] = $productUrl;
                        $quoteProductsInfo[$productData->getId()]['status'] = 0;
                    } else {
                        $minqty = $productData->getMinQuoteQty();
                        if ($minqty=='' || $minqty==null) {
                            $minqty = $this->getConfigMinQty();
                        }
                        $quoteProductsInfo[$productData->getId()]['min_qty'] = $minqty;
                        $quoteProductsInfo[$productData->getId()]['url'] = $productUrl;
                        $quoteProductsInfo[$productData->getId()]['status'] = 1;
                    }
                }
            }
        }
        return $quoteProductsInfo;
    }

    /**
     * function to get final product price
     *
     * @param  [array] $params
     * @return float $finalPrice
     */
    public function calculateProductPrice($params)
    {
        $finalPrice = 0;
        $productId = $params['product'];
        $product = $this->getProduct($productId);
        if (in_array($product->getTypeId(), ['simple', 'downloadable', 'virtual'])) {
            $finalPrice += $product->getPriceModel()->getFinalPrice(self::BASE_QTY, $product);
        } elseif ($product->getTypeId() == 'bundle') {
            $finalPrice += $this->getBundleProductPrice(
                $product,
                $params['bundle_option_to_calculate']
            );
        } elseif ($product->getTypeId() == 'configurable') {
            $finalPrice = $this->getConfigurableProductPrice($product, $params);
        }
        if (array_key_exists('links', $params) && !empty($params['links'])) {
            $finalPrice += $this->getProductPriceByLinks($product, $params['links'], $finalPrice);
        }
        if (array_key_exists('options', $params) && $params['options'] != null && count($params['options'])) {
            $finalPrice += $this->getProductOptionsPrice($product, $params['options'], $finalPrice);
        }
        return $finalPrice;
    }

    public function getProductPriceByLinks($product, $links, $basePrice)
    {
        $finalPrice = 0;
        $productLinks = $product->getTypeInstance()->getLinks($product);
        foreach ($links as $linkKey => $linkId) {
            if (isset($productLinks[$linkId])) {
                $finalPrice += $productLinks[$linkId]->getPrice();
            }
        }
        return $finalPrice;
    }

    /**
     * get bundle product final price
     *
     * @param  $product
     * @param  $bundleOptions
     * @return $finalPrice
     */
    public function getBundleProductPrice($product, $bundleOptions)
    {
        $finalPrice = 0;
        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        $optionsCollection = $product->getTypeInstance(true)
            ->getOptionsCollection($product);
        foreach ($optionsCollection as $options) {
            $optionId = $options->getOptionId();
            if (array_key_exists($optionId, $bundleOptions['bundle_option'])
                && $valueId = $bundleOptions['bundle_option'][$optionId]
            ) {
                foreach ($selectionCollection as $proselection) {
                    if (is_array($valueId)) {
                        if (in_array($proselection->getSelectionId(), $valueId)) {
                            $selectionQty = $product->getCustomOption(
                                'selection_qty_' . $proselection->getSelectionId()
                            );
                            $finalPrice += $product->getPriceModel()->getSelectionFinalTotalPrice(
                                $product,
                                $proselection,
                                self::BASE_QTY,
                                $selectionQty,
                                $multiplyQty = true,
                                $takeTierPrice = true
                            );
                        }
                    } else {
                        if ($proselection->getSelectionId() == $valueId) {
                            $selectionQty = $product->getCustomOption(
                                'selection_qty_' . $proselection->getSelectionId()
                            );
                            $finalPrice += $product->getPriceModel()->getSelectionFinalTotalPrice(
                                $product,
                                $proselection,
                                self::BASE_QTY,
                                $selectionQty,
                                $multiplyQty = true,
                                $takeTierPrice = true
                            );
                        }
                    }
                }
            }
        }
        return $finalPrice;
    }

    /**
     * get total quantity of bundle product
     *
     * @param  $product
     * @param  $bundleOptions
     * @return $quantity
     */
    public function getBundleProductQuatity($product, $bundleOptions)
    {
        $quantity = 0;
        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        $optionsCollection = $product->getTypeInstance(true)
            ->getOptionsCollection($product);
        foreach ($optionsCollection as $options) {
            $optionId = $options->getOptionId();
            if (array_key_exists($optionId, $bundleOptions['bundle_option'])
                && $valueId = $bundleOptions['bundle_option'][$optionId]
            ) {
                foreach ($selectionCollection as $proselection) {
                    $selectedProductId = 0;
                    if (is_array($valueId)) {
                        if (in_array($proselection->getSelectionId(), $valueId)) {
                            $selectedProductId = $proselection->getEntityId();
                        }
                    } else {
                        if ($proselection->getSelectionId() == $valueId) {
                            $selectedProductId = $proselection->getEntityId();
                        }
                    }
                    if ($selectedProductId) {
                        $selectedProduct = $this->getProduct($selectedProductId);
                        $quantity+=$selectedProduct->getQuantityAndStockStatus()['qty'];
                    }
                }
            }
        }
        return $quantity;
    }

    /**
     * get configurable product final price
     *
     * @param  [type] $product
     * @param  [type] $params
     * @return $finalPrice
     */
    public function getConfigurableProductPrice($product, $params)
    {
        $finalPrice = 0;
        $configurableOptions = $params['super_attribute'];

        $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);

        foreach ($attributes as $attribute) {
            $attributeModel = $this->_eavConfig->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $attribute['attribute_id']
            );
            $attributeCode = $attributeModel->getAttributeCode();
            $attributeArray[$attribute['attribute_id']] = $attributeCode;
        }
        $productPrice = 0;
        $productIdArray = [];
        $productWithAttributes = $product->getTypeInstance(true)->getUsedProducts($product);
        foreach ($productWithAttributes as $usedProduct) {
            $flag = 0;
            foreach ($attributeArray as $attrId => $attributeCode) {
                if (array_key_exists($attributeCode, $usedProduct->getData())) {
                    if (in_array($usedProduct[$attributeCode], $configurableOptions)) {
                        $productIdArray[] = $usedProduct->getEntityId();
                    }
                }
            }
        }
        $counts = array_count_values($productIdArray);
        arsort($counts);
        $configurableProductId = key($counts);
        $configurableProduct = $this->getProduct($configurableProductId);
        $finalPrice += $configurableProduct->getPrice();
        return $finalPrice;
    }

    /**
     * get configurable product product quantity
     *
     * @param  [type] $product
     * @param  [type] $quote
     * @return $qty
     */
    public function getConfigurableProductQuantity($product, $quote)
    {
        $productQty = 0;
        $configurableOptions = $this->convertStringAccToVersion($quote->getSuperAttribute(), 'decode');
        
        $childProducts = $this->_typeConfigurable->getUsedProducts($product);
        foreach ($childProducts as $child) {
            foreach ($configurableOptions as $key => $configurableOption) {
                $configattr = $child->getData($this->_attribute->load($key)->getAttributeCode());
                if ($configattr == $configurableOption) {
                    $childProduct = $this->getProduct($child->getEntityId());
                    $productQty = $childProduct->getQuantityAndStockStatus()['qty'];
                }
            }
        }
        return $productQty;
    }

    /**
     * get product option price
     *
     * @param  $product
     * @param  $productOptions
     * @param  $basePrice
     * @return $finalPrice
     */
    public function getProductOptionsPrice($product, $productOptions, $basePrice)
    {
        $finalPrice = 0;
        $options = $productOptions;
        if (!is_array($options)) {
            return $finalPrice;
        }
        $productOptions = $product->getOptions();
        foreach ($options as $key => $option) {
            foreach ($productOptions as $proOption) {
                if ($proOption->getOptionId() == $key) {
                    $group = $proOption->groupFactory($proOption->getType())
                        ->setOption($proOption)
                        ->setConfigurationItemOption($proOption);
                    
                    if ($proOption->getType()=='checkbox' || $proOption->getType()=='multiple') {
                        if (is_array($option)) {
                            $option = implode(',', $option);
                        }
                    }
                    $finalPrice += $group->getOptionPrice($option, $basePrice);
                }
            }
        }
        return $finalPrice;
    }

    protected function finalPriceByProOption($proOption, $option)
    {
        $finalPrice = 0;
        if (in_array($proOption->getType(), ['area', 'field'])) {
            $finalPrice += $proOption->getPrice();
            return $finalPrice;
        } elseif (in_array($proOption->getType(), ['drop_down', 'radio'])) {
            $optionValues = $proOption->getValues();
            foreach ($optionValues as $optVal) {
                if ($option == $optVal->getOptionTypeId()) {
                    $finalPrice += $optVal->getPrice();
                    return $finalPrice;
                }
            }
        } elseif (in_array($proOption->getType(), ['multiple', 'checkbox'])) {
            $optionValues = $proOption->getValues();
            foreach ($optionValues as $optVal) {
                if (in_array($optVal->getOptionTypeId(), $option)) {
                    $finalPrice += $optVal->getPrice();
                    return $finalPrice;
                }
            }
        } else {
            $finalPrice = $this->finalPriceByDateTime($proOption, $finalPrice);
            return $finalPrice;
        }
    }

    protected function finalPriceByDateTime($proOption, $finalPrice)
    {
        if ($proOption->getType() == 'date_time') {
            $finalPrice += $proOption->getPrice();
            return $finalPrice;
        } elseif ($proOption->getType() == 'date') {
            $finalPrice += $proOption->getPrice();
            return $finalPrice;
        } elseif ($proOption->getType() == 'time') {
            $finalPrice += $proOption->getPrice();
            return $finalPrice;
        }
    }

    /**
     * get config settings for redirect url after adding product to cart
     */
    public function getRedirectConfigSetting()
    {
        return $this->scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get all config values array
     *
     * @return array
     */
    public function getConfigValues()
    {
        $sectionId = 'quotesystem';
        $groupId = 'mot_quotesystemsetting';
        $optionArray = 'mot_quotesystemenabledisable';
        $value = $sectionId.'/'.$groupId.'/'.$optionArray;
        $values = $this->scopeConfig->isSetFlag($value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $values;
    }

    /**
     * Get module status
     */
    public function checkModuleIsEnabledOrNot($moduleName)
    {
        if ($this->moduleManager->isEnabled($moduleName)) {
            if ($this->moduleManager->isOutputEnabled($moduleName)) {
                return true;
            }
        }
        return false;
    }

    public function convertStringAccToVersion($string, $type)
    {
        if ($string!='') {
            $moduleData = $this->moduleList->getOne('Motus_Quotesystem');
            $moduleVersion = $moduleData['setup_version'];
            $magentoVersion = $this->productMetaData->getVersion();
            if (version_compare($moduleVersion, '2.0.2')>=0) {
                if ($type=='encode') {
                    return json_encode($string);
                } else {
                    $object = json_decode($string);
                    if (is_object($object)) {
                        return json_decode(json_encode($object), true);
                    }
                    return $object;
                }
            } else {
                if ($type=='encode') {
                    return $this->serializeInterface->serialize($string);
                } else {
                    return $this->serializeInterface->unserialize($string);
                }
            }
        }
        return $string;
    }

    /**
     * check quote product in cart
     *
     * @param  $item
     * @return boolean
     */
    public function checkQuoteProductinItem($item)
    {
        if ($item->getItemId()) {
            $quoteCollection = $this->getMotQuoteModel()->getCollection();
            $quoteCollection->addFieldToFilter('item_id', $item->getItemId());
            if ($quoteCollection->getSize()) {
                return true;
            }
        }
        return false;
    }

    /**
     * get if item is quote product
     *
     * @param  $item
     * @return boolean
     */
    public function isQuoteItem($item)
    {
        $quoteId = 0;
        $params = $this->_request->getParams();
        if (is_array($params) && array_key_exists('quote_id', $params) && $params['quote_id']>0) {
            $quoteId = $params['quote_id'];
        }
        return $quoteId;
    }

    /**
     * get base currency symbol
     *
     * @return character
     */
    public function getBaseCurrencySymbol()
    {
        return $this->_localeCurrency->getCurrency(
            $this->getBaseCurrencyCode()
        )->getSymbol();
    }

    public function validateBundleProductQuantity($product, $bundleOptions, $quote, $wholedata)
    {
        $quantity = 0;
        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        $optionsCollection = $product->getTypeInstance(true)
            ->getOptionsCollection($product);
        foreach ($optionsCollection as $options) {
            $optionId = $options->getOptionId();
            if (array_key_exists($optionId, $bundleOptions['bundle_option'])
                && $valueId = $bundleOptions['bundle_option'][$optionId]
            ) {
                foreach ($selectionCollection as $proselection) {
                    $selectedProductId = 0;
                    if (is_array($valueId)) {
                        if (in_array($proselection->getSelectionId(), $valueId)) {
                            $selectedProductId = $proselection->getEntityId();
                        }
                    } else {
                        if ($proselection->getSelectionId() == $valueId) {
                            $selectedProductId = $proselection->getEntityId();
                        }
                    }
                    if ($selectedProductId) {
                        $selectedProduct = $this->getProduct($selectedProductId);
                        $quantity = $selectedProduct->getQuantityAndStockStatus()['qty'];
                        $bundleProductQty = $bundleOptions['bundle_option_qty'][$optionId];
                        $checkQuantity = $quote->getQuoteQty();
                        if (array_key_exists('quote_qty', $wholedata) && $checkQuantity != $wholedata['quote_qty']) {
                            $checkQuantity = $wholedata['quote_qty'];
                        }
                        if ($bundleProductQty * $checkQuantity > $quantity) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    public function checkProductCanShowOrNot($product, $where = 'catalog')
    {
        return $this->catalogProductHelper->canShow($product, $where);
    }

    /**
     * get coupon discount enable on quote product
     *
     * @return boolean
     */
    public function getDiscountEnable()
    {
        return $this->scopeConfig->getValue(
            'quotesystem/mot_quotesystemsetting/discount_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * funnction to check product in quote is quote applied
     *
     * @return boolean
     */
    public function checkQuoteProductIsInCart()
    {
        if ($this->customerSession->isLoggedIn()) {
            $cart = $this->_checkoutSession
                ->getQuote()
                ->getAllItems();
            $productIds = [];
            if (count($cart)) {
                foreach ($cart as $item) {
                    $result = $this->checkQuoteProductinItem($item);
                    if ($result) {
                        return true;
                    }
                }
                return false;
            }
        }
        return false;
    }

    /**
     * apply discount on quote product after checking
     *
     * @param  [int] $item
     * @return boolean
     */
    public function checkAndUpdateForDiscount($item)
    {
        $result = $this->checkQuoteProductinItem($item);
        if ($result && !$this->getDiscountEnable()) {
            return true;
        }
        return false;
    }

    /**
     * get media url
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
    }

    /**
     * @param string|null $attachments
     * @return array
     */
    public function getQuoteAttachmentsArr($attachments)
    {
        $attachmentsArr = [];
        if (!empty($attachments)) {
            $attachmentsArrData = explode(',', $attachments);
            foreach ($attachmentsArrData as $attachment) {
                $attachmentArr = explode('/', $attachment);
                $index = count($attachmentArr) - 1;
                if (isset($attachmentArr[$index])) {
                    $attachmentsArr[$attachment] = $attachmentArr[$index];
                } else {
                    $attachmentsArr[$attachment] = $attachment;
                }
            }
        }
        return $attachmentsArr;
    }

    /**
     * get current currency symbol
     *
     * @return string
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->_localeCurrency->getCurrency(
            $this->getCurrentCurrencyCode()
        )->getSymbol();
    }

    /**
     * get current currency price from base currency
     *
     * @param  [float] $price
     * @return float
     */
    public function getCurrentCurrencyPrice($price, $to = null)
    {
        if (!$to) {
            /*
            * Get Current Store Currency Rate
            */
            $currentCurrencyCode = $this->getCurrentCurrencyCode();
        } else {
            $currentCurrencyCode = $to;
        }
        $baseCurrencyCode = $this->getBaseCurrencyCode();
        $allowedCurrencies = $this->getConfigAllowCurrencies();
        $rates = $this->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if (empty($rates[$currentCurrencyCode])) {
            $rates[$currentCurrencyCode] = 1;
        }
        return $price * $rates[$currentCurrencyCode];
    }

    /**
     * get base currency price from current currency
     *
     * @param  [float] $price
     * @return float
     */
    public function getBaseCurrencyPrice($price, $from = null)
    {
        if (!$from) {
            /*
            * Get Current Store Currency Rate
            */
            $currentCurrencyCode = $this->getCurrentCurrencyCode();
        } else {
            $currentCurrencyCode = $from;
        }
        $baseCurrencyCode = $this->getBaseCurrencyCode();
        $allowedCurrencies = $this->getConfigAllowCurrencies();
        $rates = $this->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if (empty($rates[$currentCurrencyCode])) {
            $rates[$currentCurrencyCode] = 1;
        }
        return $price / $rates[$currentCurrencyCode];
    }

    /**
     * getAllowedFileTypes
     *
     * @return string
     */
    public function getAllowedFileTypes()
    {
        return $this->scopeConfig->getValue(
            'quotesystem/mot_quotesystemsetting/allowed_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * validateFiles
     *
     * @param  array $files
     * @return array
     */
    public function validateFiles($files)
    {
        $errors = [];
        if (!empty($files)) {
            foreach ($files as $name => $data) {
                $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($fileInfo, $data['tmp_name']);
                if (!empty($data['tmp_name'])
                    && (!empty($data['type']))
                ) {
                    if (strpos($data['type'], 'image') !== false) {
                        $isValid = getimagesize($data['tmp_name']);
                        if ($isValid == false && strpos($mimeType, 'image') === false) {
                            $errors[] = __("%1 is not a valid image file", $data['name']);
                        }
                    } elseif ($data['type'] == "application/pdf" && $mimeType!=="application/pdf") {
                        $errors[] = __("%1 is not a valid pdf file", $data['name']);
                    } elseif ($data['type'] == "application/msword" && $mimeType!=="application/msword") {
                        $errors[] = __("%1 is not a valid doc file", $data['name']);
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * get current store currency
     *
     * @return string currencycode
     */
    public function getCurrentCurrency()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencyCode();
    }

    /**
     * get currency symbol
     *
     * @param  [string] $currencyCode
     * @return string symbol
     */
    public function getCurrencySymbol($currencyCode)
    {
        return $this->_localeCurrency->getCurrency(
            $currencyCode
        )->getSymbol();
    }

    /**
     * function to get formatted price in given currency
     *
     * @param  [float]  $amount
     * @param  [string] $currency
     * @return void
     */
    public function getFormatPrice($amount, $currency)
    {
        return $this->priceCurrency->format(
            $amount,
            true,
            $this->priceCurrency::DEFAULT_PRECISION,
            null,
            $currency
        );
    }

    /**
     * function to get Add To Cart config value
     *
     * @return boolean
     */
    public function getConfigAddToCart()
    {
        if ($this->getModuleStatus()) {
            return $this->scopeConfig->getValue(
                'quotesystem/mot_quotesystemsetting/allowed_add_to_cart',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * function to get Price display value from config
     *
     * @return boolean
     */
    public function getConfigShowPrice()
    {
        if ($this->getModuleStatus()) {
            return $this->scopeConfig->getValue(
                'quotesystem/mot_quotesystemsetting/allowed_showprice',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * return module status
     *
     * @return boolean
     */
    public function getModuleStatus()
    {
        return $this->scopeConfig->getValue(
            'quotesystem/mot_quotesystemsetting/mot_quotesystemenabledisable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * function to get Minimum Config Quote Qty
     *
     * @return int
     */
    public function getConfigMinQty() : int
    {
        return (int)$this->scopeConfig->getValue(
            'quotesystem/mot_quotesystemsetting/min_quote_qty',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * function to remove price info from html
     *
     * @param  [string] $html
     * @return string
     */
    public function removePriceInfo($html) : string
    {
        $newDom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $newDom->loadHTML($html);
        $spanTags = $newDom->getElementsByTagName('span');
        for ($i = $spanTags->length; --$i >= 0;) {
            $span = $spanTags->item($i);
            $span->parentNode->removeChild($span);
        }
        return $newDom->saveHTML();
    }

    /**
     * function to get Product from repository
     *
     * @param [int] $id
     */
    public function getProductById($id)
    {
        return $this->productRepository->getById($id);
    }

    /**
     * remove item from cart of which quote is unapproved
     *
     * @param array $id
     */
    public function removeCartItem($id)
    {
        $quote = $this->quote->create();
        $quoteFactory = $this->quoteItem->create();
        foreach ($id as $cartItemId) {
            $quoteCollection = $this->getMotQuoteModel()->load($cartItemId);
            if ($quoteCollection->getSize()) {
                $customerId = $quoteCollection->getCustomerId();
                $productId = $quoteCollection->getProductId();
                $quoteData = $quote->getCollection()
                            ->addFieldToFilter('customer_id', $customerId)
                            ->addFieldToFilter('is_active', 1);
                $data = $quoteData->getData();
                $quoteId = $data[0]['entity_id'];
                $quoteItemQty = $data[0]['items_qty'];
                if ($quoteId) {
                    $quoteItemData = $quoteFactory->getCollection()
                                                ->addFieldToFilter('quote_id', $quoteId)
                                                ->addFieldToFilter('product_id', $productId);
                    $qty = $quoteItemData->getData()[0]['qty'];
                    removeItem($quoteItemData, $quoteItemQty, $qty, $quoteData, $quote);
                }
            }
        }
    }

    /**
     * remove item from cart
     *
     * @param \Magento\Quote\Model\Quote\ItemFactory $quoteItemData
     * @param int $quoteItemQty
     * @param int $qty
     * @param array $quoteData
     * @param \Magento\Quote\Model\QuoteFactory $quote
     */
    public function removeItem($quoteItemData, $quoteItemQty, $qty, $quoteData, $quote)
    {
        if ($quoteItemData) {
            if ($quoteItemQty == $qty) {
                foreach ($quoteData as $item) {
                    $item->delete();
                }
            } else {
                $dta = $quote->load($quoteId);
                $dta->setItemsQty($quoteItemQty-$qty);
                $dta->save();
            }
            foreach ($quoteItemData as $item) {
                $item->delete();
            }
        }
    }

    /**
     * Get loaded product list
     */
    public function getLoadedProduct()
    {
        $productCollection = $this->productList;
        return $productCollection->getLoadedProductCollection();
    }

    /**
     * checks if ShowPriceAfterLogin System Module is active
     *
     * @return boolean
     */
    public function isShowPriceAfterLoginEnabled()
    {
        return $this->moduleManager->isOutputEnabled("Motus_ShowPriceAfterLogin");
    }
}
