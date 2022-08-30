<?php
/**
 * Quote Add to cart action when customer adds a quote to cart.
 */

namespace Motus\Quotesystem\Controller\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Motus\Quotesystem\Model\QuotesFactory;
use Motus\Quotesystem\Helper\Data;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Motus\Quotesystem\Api\QuoteRepositoryInterface;
use Magento\Customer\Model\Url;

class Addtocart extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var QuotesFactory
     */
    protected $_quotesFactory;
    /**
     * @var Helper\Data
     */
    protected $_helper;
    /**
     * @var Magento\Checkout\Model\Cart
     */
    protected $_cartModel;
    /**
     * @var Checkout/Session
     */
    protected $_checkoutSession;
    /**
     * @var Motus\Quotesystem\Api\QuoteRepositoryInterface
     */
    protected $_quoteRepository;
    /**
     * @var  Magento\Customer\Model\Url
     */
    protected $_customerUrl;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;
    /**
     * @param Context                         $context
     * @param PageFactory                     $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param QuotesFactory                   $quotesFactory
     * @param Data                            $helper
     * @param cart                            $cartModel
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param QuoteRepositoryInterface        $quoteRepositoryInterface
     * @param Url                             $customerUrl
     */

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        QuotesFactory $quotesFactory,
        Data $helper,
        cart $cartModel,
        \Magento\Checkout\Model\Session $checkoutSession,
        QuoteRepositoryInterface $quoteRepositoryInterface,
        Url $customerUrl,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_quotesFactory = $quotesFactory;
        $this->_helper = $helper;
        $this->_cartModel = $cartModel;
        $this->_checkoutSession = $checkoutSession;
        $this->_quoteRepository = $quoteRepositoryInterface;
        $this->_customerUrl = $customerUrl;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_customerUrl->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Default customer account page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $result = [];
        if (!array_key_exists('quote_id', $data)) {
            $result['error'] = 1;
            $result['message'] = __('Quote does not exist, please contact admin.');
            return $this->getResponse()->representJson(
                $this->_jsonHelper->jsonEncode($result)
            );
        }
        $quote = $this->_quoteRepository->getById($data['quote_id']);
        $quoteProductId = $quote->getProductId();
        $customerId = $this->_customerSession->getCustomerId();
        $session = $this->_checkoutSession;
        if ($this->checkQuoteAlreadyAddedOrNot($session, $quoteProductId)) {
            $result['error'] = 1;
            $result['message'] = __('A Quote item of same product is already added in cart.');
            return $this->getResponse()->representJson(
                $this->_jsonHelper->jsonEncode($result)
            );
        }
        $productAddToCart = $this->_helper->getProduct($quote->getProductId());
        $params = [];
        if (in_array(
            $productAddToCart->getTypeId(),
            ['simple', 'virtual', 'downloadable', 'configurable']
        )
        ) {
            //creating custom options to add
            $optionToAdd = $this->getQuoteOptionsData($quote);
        }
        if ($productAddToCart->getTypeId() == 'configurable') {
            $superAttribute = $this->_helper->convertStringAccToVersion($quote->getSuperAttribute(), 'decode');
            $params = [
                    'product' => $quote->getProductId(),
                    'qty' => $quote->getQuoteQty(),
                    'super_attribute' => $superAttribute,
                    'options' => $optionToAdd,
                    'quote_id' =>$data['quote_id'],
                ];
        } elseif ($productAddToCart->getTypeId() == 'bundle') {
            $bundleOptionArray = $this->_helper->convertStringAccToVersion($quote->getBundleOption(), 'decode');
            $bundleOption = $bundleOptionArray['bundle_option'];
            $bundleOptionQty = $bundleOptionArray['bundle_option_qty'];
            $params = [
                    'product' => $quote->getProductId(),
                    'qty' => $quote->getQuoteQty(),
                    'bundle_option' => $bundleOption,
                    'bundle_option_qty' => $bundleOptionQty,
                    'quote_id' =>$data['quote_id'],
                ];
        } elseif ($productAddToCart->getTypeId() == 'downloadable') {
            $params = [
                    'product' => $quote->getProductId(),
                    'qty' => $quote->getQuoteQty(),
                    'options' => $optionToAdd,
                    'links' => $this->_helper->convertStringAccToVersion($quote->getLinks(), 'decode'),
                    'quote_id' =>$data['quote_id'],
                ];
        } elseif (in_array($productAddToCart->getTypeId(), ['simple', 'virtual'])) {
            $params = [
                    'product' => $quote->getProductId(),
                    'qty' => $quote->getQuoteQty(),
                    'options' => $optionToAdd,
                    'quote_id' =>$data['quote_id'],
                ];
        }
        try {
            $cart = $this->_cartModel;
            $cart->addProduct($productAddToCart, $params);
            $cart->save();
            $result['error'] = 0;
            $configSetting = $this->_helper->getRedirectConfigSetting();
            if ($configSetting==0) {
                $result['redirecturl'] = '';
            } else {
                $result['redirecturl'] = $this->_url->getUrl('checkout/cart');
            }
            $result['message'] = __('Quote Product is added into cart');
            return $this->getResponse()->representJson(
                $this->_jsonHelper->jsonEncode($result)
            );
        } catch (\Exception $e) {
            $result['error'] = 1;
            $result['message'] = $e->getMessage();
            return $this->getResponse()->representJson(
                $this->_jsonHelper->jsonEncode($result)
            );
        }
    }
    protected function goBack($backUrl = null, $product = null)
    {
        $redirectUrl = $this->_url->getUrl('customer/account');
        if (!$this->getRequest()->isAjax()) {
            if ($backUrl || $backUrl = $redirectUrl) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($backUrl);
                return $resultRedirect;
            }
        }

        $result = [];

        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            }
        }

        $this->getResponse()->representJson(
            $this->_jsonHelper->jsonEncode($result)
        );
    }

    /**
     * checkQuoteAlreadyAddedOrNot check whether a quote product already added to cart or not
     *
     * @param checkoutSession $session
     * @param int             $quoteProductId
     */
    public function checkQuoteAlreadyAddedOrNot($session, $quoteProductId)
    {
        foreach ($session->getQuote()->getAllItems() as $item) {
            if ($quoteProductId == $item->getProductId()) {
                if ($item->getParentItemId() === null && $item->getItemId() > 0) {
                    $quoteCollection = $this->_helper->getMotQuoteModel()
                        ->getCollection()
                        ->addFieldToFilter("item_id", $item->getItemId())
                        ->addFieldToFilter("product_id", $item->getProductId());
                    if ($quoteCollection->getSize()) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getQuoteOptionsData($quote)
    {
        $savedOptions = $this->_helper->convertStringAccToVersion($quote->getProductOption(), 'decode');
        $optionToAdd = [];
        if (is_array($savedOptions)) {
            foreach ($savedOptions as $key => $value) {
                $optionToAdd[$key] = $value;
            }
        }
        return $optionToAdd;
    }
}
