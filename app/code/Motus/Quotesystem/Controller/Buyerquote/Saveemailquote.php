<?php
/**
 * Save and Email quote at customer end.
 */

namespace Motus\Quotesystem\Controller\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\ProductFactory;
use Motus\Quotesystem\Helper;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Customer\Model\Url;
use Motus\Quotesystem\Api\QuoteRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Savemailquote extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalogProduct;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Motus\Quotesystem\Model\QuotesFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Motus\Quotesystem\Helper\Mail
     */
    protected $_mailHelper;

    /**
     * File Uploader factory.
     *
     * @var \Motus\Quotesystem\Helper\Data
     */
    protected $_helper;

    /**
     * @var Motus\Quotesystem\Api\QuoteRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var TimezoneInterface
     */
    protected $_timezoneinterface;

    /**
     * @var Magento\Framework\Session\SessionManager
     */
    private $_session;

    /**
     * @var [StockItemRepository]
     */
    private $_stockItemRepository;

    /**
     * @var [Configurable]
     */
    private $_configurableProduct;

    /**
     * @param Context                                                   $context
     * @param ProductFactory                                            $catalogProduct
     * @param \Magento\Customer\Model\Session                           $customerSession
     * @param \Magento\Framework\Json\Helper\Data                       $jsonHelper
     * @param \Motus\Quotesystem\Model\QuotesFactory                   $quotes
     * @param Helper\Mail                                               $helperMail
     * @param Helper\Data                                               $helper
     * @param QuoteRepositoryInterface                                  $quoteRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime               $date
     * @param TimezoneInterface                                         $timezoneinterface
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
     * @param Configurable                                              $configurableProduct
     */
    public function __construct(
        Context $context,
        ProductFactory $catalogProduct,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Motus\Quotesystem\Model\QuotesFactory $quotes,
        \Motus\Quotesystem\Model\QuoteDetailsFactory $quoteDetails,
        Helper\Mail $helperMail,
        Helper\Data $helper,
        QuoteRepositoryInterface $quoteRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        TimezoneInterface $timezoneinterface,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        Configurable $configurableProduct
    ) {
        $this->_customerSession = $customerSession;
        $this->_catalogProduct = $catalogProduct;
        $this->_quoteFactory = $quotes;
        $this->_quoteDetailsFactory = $quoteDetails;
        $this->_jsonHelper = $jsonHelper;
        $this->_mailHelper = $helperMail;
        $this->_helper = $helper;
        $this->_date = $date;
        $this->_timezoneinterface = $timezoneinterface;
        $this->_quoteRepository = $quoteRepository;
        $this->_stockItemRepository = $stockItemRepository;
        $this->_configurableProduct = $configurableProduct;
        parent::__construct($context);
    }

    /**
     * Save quote from buyer.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $result = [];
        if (!$this->getRequest()->isPost() || !$this->_customerSession->isLoggedIn()) {
            $redirectUrl = $this->_url->getUrl('quotesystem/buyerquote/index/');
            $this->messageManager->addError(
                __(
                    "Sorry some error occured!!!"
                )
            );
            return $this->goBack($redirectUrl);
        }
        $params = $this->getRequest()->getParams();

        if (!is_array($params)) {
            $this->messageManager->addError(
                __("Sorry!! Quote can't be saved.")
            );
            return $this->goBack($params['current_url']);
        }

        $errors = $this->validateData($params);
        if (empty($errors)) {
            $productId = $this->productIdByProductType($params);
            $product = $this->_catalogProduct->create()->load($productId);
            $productQty = $this->selectedProductQty($product, $params);
            $mainProductId = 0;
            if ($productId != $params['product']) {
                $mainProductId = $params['product'];
            }
            
            if ($productQty==0 && $product->getTypeId() != 'downloadable') {
                $this->messageManager
                    ->addError(__("Sorry!! Quantity of this product in stock is zero."));
                return $this->goBack($params['current_url']);
            }
            $finalPrice = $this->getProductPrice($params, $product);
            $productOptions = [];
            $fileNames = [];
            $lastQuoteId = 0;
            $bundleOption = [];
            $request = new \Magento\Framework\DataObject($params);
            $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced(
                $request,
                $product
            );
            if (is_string($cartCandidates) || $cartCandidates instanceof \Magento\Framework\Phrase) {
                $result['error'] = 1;
                $result['message'] = (string)__($cartCandidates);
                return $this->getResponse()->representJson(
                    $this->_jsonHelper->jsonEncode($result)
                );
            }
            if ($mainProductId != 0) {
                $mainProduct = $this->_helper->getProduct($mainProductId);
                $quoteMinimumQty = $mainProduct->getMinQuoteQty();
            } else {
                $quoteMinimumQty = $product->getMinQuoteQty();
            }
            // get config quote qty
            if (!$quoteMinimumQty) {
                $quoteMinimumQty = $this->_helper->getConfigMinQty();
            }

            if ($quoteMinimumQty <= $params['quote_qty']) {
                $bundleOption = $this->setBundleOption($params, $product);
                $params['bundle_option_to_calculate'] = $bundleOption;
                $productOptions = $this->setProductOption($params);
                $params['quote_description'] = trim($params['quote_description']);
                $params['quote_description'] = strip_tags($params['quote_description']);
                $attachments = '';
                if (isset($params['attachments']) && is_array($params['attachments'])) {
                    $attachments = implode(',', $params['attachments']);
                }
                $quotePrice = $this->_helper->getBaseCurrencyPrice($params['quote_price']);
                $customerId = $this->_customerSession->getCustomerId();
                $quoteDetailsColl = $this->_quoteDetailsFactory->create()->getCollection()
                                                    ->addFieldToFilter('customer_id', $customerId)
                                                    ->addFieldToFilter('is_active', 1);
                
                if ($quoteDetailsColl->getSize()) {
                    $quoteId = $quoteDetailsColl->getFirstItem()->getId();
                } else {
                    $quoteDetails = $this->_quoteDetailsFactory->create()
                        ->setCustomerId($customerId)
                        ->setCreatedAt(time())
                        ->setIsActive(1);
                    $quoteId = $quoteDetails->save()->getId();
                }
                
                $quote = $this->_quoteFactory->create()
                    ->setProductId($params['product'])
                    ->setProductName($product->getName())
                    ->setProductPrice($finalPrice)
                    ->setProductOption($this->_helper->convertStringAccToVersion($productOptions, 'encode'))
                    ->setCustomerId($customerId)
                    ->setQuoteQty($params['quote_qty'])
                    ->setQuotePrice($params['quote_price'])
                    ->setQuoteDesc($params['quote_description'])
                    ->setStatus(\Motus\Quotesystem\Model\Quotes::STATUS_UNAPPROVED)//set pending status
                    ->setCreatedAt(time())
                    ->setAttachments($attachments)
                    ->setQuoteCurrency($this->_helper->getCurrentCurrency())
                    ->setQuoteId($quoteId);
                if (isset($params['links'])) {
                    $quote->setLinks($this->_helper->convertStringAccToVersion($params['links'], 'encode'));
                }
                if (isset($bundleOption)) {
                    $quote->setBundleOption($this->_helper->convertStringAccToVersion($bundleOption, 'encode'));
                }
                if (isset($params['super_attribute'])) {
                    $quote->setSuperAttribute(
                        $this->_helper->convertStringAccToVersion($params['super_attribute'], 'encode')
                    );
                }
                $lastQuoteId = $quote->save()->getEntityId();
                // send mail
                $this->_mailHelper->newQuote($lastQuoteId);

                $this->messageManager
                    ->addSuccess(__("Your Quote item has been successfully added"));
                return $this->goBack($params['current_url']);
            } else {
                $this->messageManager
                    ->addError(__("Sorry you are not allowed to quote such a low quantity"));
                return $this->goBack($params['current_url']);
            }
        } else {
            foreach ($errors as $message) {
                $this->messageManager->addError($message);
            }
            if (!array_key_exists('current_url', $params)) {
                return $this->goBack('');
            }
            return $this->goBack($params['current_url']);
        }
    }

    /**
     * Get ProductPrice According to SpecialPrice, TierPrice and Custom Option.
     *
     * @return price
     */
    public function getProductPrice($params, $product)
    {
        if (array_key_exists('bundle_option', $params) && $params['bundle_option']) {
            $bundleOption = $this->setBundleOption($params, $product);
            $params['bundle_option_to_calculate'] = $bundleOption;
        }
        $productPrice = $this->_helper->calculateProductPrice(
            $params
        );
        return($productPrice);
    }

    /**
     * setProductOption
     *
     * @param  array $params
     * @return mixed
     */
    protected function setProductOption($params)
    {
        if (isset($params['options'])) {
            foreach ($params['options'] as $key => $value) {
                if (empty($value)) {
                    unset($params['options'][$key]);
                }
            }
            return $params['options'];
        }
    }

    /**
     * setBundleOption
     *
     * @param  array                          $params
     * @param  \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function setBundleOption($params, $product)
    {
        if (isset($params['bundle_option'])) {
            $bundleOption = $this->getBundleProductData($params, $product);
            return $bundleOption;
        }
    }

    /**
     * productIdByProductType
     *
     * @param  array $params
     * @return mixed
     */
    protected function productIdByProductType($params)
    {
        if (array_key_exists('selected_configurable_option', $params) &&
        $params['selected_configurable_option'] != "") {
            return $params['selected_configurable_option'];
        } elseif (array_key_exists('super_attribute', $params) && $params['super_attribute'] != "") {
            $product = $this->_catalogProduct->create()->load($params['product']);
            return $this->_configurableProduct
                ->getProductByAttributes(
                    $params['super_attribute'],
                    $product
                )->getId();
        } else {
            if (array_key_exists('product', $params) && $params['product'] != "") {
                return $params['product'];
            }
        }
    }

    /**
     * return $result with back url in json format.
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    protected function goBack($backUrl = null)
    {
        $redirectUrl = '';
        if (!$this->getRequest()->isAjax()) {
            $redirectUrl = $this->_url->getUrl('customer/account');
            if ($backUrl || $backUrl = $redirectUrl) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($backUrl);
                return $resultRedirect;
            }
        }
        $result = [];
        if ($backUrl || $backUrl = $redirectUrl) {
            $result['backUrl'] = $backUrl;
        }
        $this->getResponse()->representJson(
            $this->_jsonHelper->jsonEncode($result)
        );
    }

    /**
     * validates quote's data added by customer.
     *
     * @return bool
     */
    public function validateData(&$params)
    {
        $errors = [];
        $data = [];
        foreach ($params as $code => $value) {
            switch ($code) {
                case 'quote_qty':
                    $validator = new \Zend_Validate_Int();
                    if (!$validator->isValid($value)) {
                        $errors[] = __('Quote Quantity can contain only integer value');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $params[$code] = $value;
                    }
                    break;
                case 'quote_price':
                    $validator = new \Zend_Validate_Float();
                    if (!$validator->isValid($value)) {
                        $errors[] = __('Quote Price can contain only decimal or integer value');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $params[$code] = $value;
                    }
                    break;
                case 'quote_description':
                    if (trim($value) == '') {
                        $errors[] = __('Please enter the quote description');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $params[$code] = $value;
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * getBundleProductData
     *
     * @param  array                          $params
     * @param  \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getBundleProductData($params, $product)
    {
        $bundleOption = [];
        if (array_key_exists('bundle_option', $params)) {
            $bundleOption['bundle_option'] = $params['bundle_option'];
        }
        if ($product->getEntityId() && $product->getTypeId() == 'bundle') {
            $selectionCollection = $product
                ->getTypeInstance(true)
                ->getSelectionsCollection(
                    $product->getTypeInstance(true)->getOptionsIds($product),
                    $product
                );
            foreach ($bundleOption['bundle_option'] as $optionkey => $optionValue) {
                foreach ($selectionCollection as $selectionValue) {
                    $selectedOption = $selectionValue->getOptionId();
                    $selectionId = $selectionValue->getSelectionId();
                    $selectionQty = $selectionValue->getSelectionQty();
                    if ($selectedOption == $optionkey && $selectionId == $optionValue) {
                        if (array_key_exists('bundle_option_qty', $params) &&
                        isset($params['bundle_option_qty']) &&
                        array_key_exists($optionkey, $params['bundle_option_qty'])) {
                            $bundleOption['bundle_option_qty'][$optionkey] = $params['bundle_option_qty'][$optionkey];
                        } else {
                            $bundleOption['bundle_option_qty'][$optionkey] = $selectionQty;
                        }
                        $bundleOption['bundle_option_price'][$optionkey] = $selectionValue->getPrice();
                        $bundleOption['bundle_option_product'][$optionkey] = $selectionValue->getEntityId();
                    }
                }
            }
        }
        return $bundleOption;
    }

    /**
     * selectedProductQty
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  array                          $params
     * @return int
     */
    public function selectedProductQty($product, $params)
    {
        if (isset($params['bundle_option']) && $params['bundle_option']) {
            $bundleOption = $this->setBundleOption($params, $product);
            $quantity = $this->_helper->getBundleProductQuatity(
                $product,
                $bundleOption
            );
            return $quantity;
        }
        if (isset($params['links'])) {
            $quantity = $params['quote_qty'];
            return $quantity;
        }
        $quantity = $product->getQuantityAndStockStatus()['qty'];
        return $quantity;
    }
}
