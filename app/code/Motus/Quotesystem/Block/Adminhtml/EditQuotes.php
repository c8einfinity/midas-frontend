<?php
/**
 * edit block qhen admin edits a quote
 */

namespace Motus\Quotesystem\Block\Adminhtml;

use Motus\Quotesystem\Model\ResourceModel\Quoteconversation;
use Magento\Framework\Pricing\Helper\Data;
use Motus\Quotesystem\Model\QuotesFactory;
use Motus\Quotesystem\Model\QuoteconversationFactory;
use Magento\Catalog\Model\ProductFactory;
use Motus\Quotesystem\Api\QuoteRepositoryInterface;

class EditQuotes extends \Magento\Framework\View\Element\Template
{
    /**
     * @var customerSession
     */
    protected $_customerSession;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customerModel;
    /**
     * @var quoteCollection
     */
    protected $_quoteConversationCollection;
    /**
     * @var pricingHelper
     */
    protected $_pricingHelper;
    /**
     * @var _quotesFactory
     */
    protected $_quotesFactory;
    /**
     * @var _productFactory
     */
    protected $_productFactory;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;
    /**
     * @var _quoteConversationCollectionFactory
     */
    protected $_quoteConversationCollectionFactory;
    /**
     * @var _quoteconversationFactory
     */
    protected $_quoteconversationFactory;
    /**
     * @var Motus\Quotesystem\Api\QuoteRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @param \Magento\Customer\Model\Session        $customerSession
     * @param \Magento\Customer\Model\Customer       $customerModel
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param Quoteconversation\CollectionFactory    $conversationCollectionFactory
     * @param QuotesFactory                          $_quotesFactory
     * @param ProductFactory                         $productFactory
     * @param QuoteconversationFactory               $conversationFactory
     * @param Data                                   $pricingHelper
     * @param QuoteRepositoryInterface               $quoteRepository
     * @param array                                  $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customerModel,
        Quoteconversation\CollectionFactory $conversationCollectionFactory,
        QuotesFactory $_quotesFactory,
        ProductFactory $productFactory,
        QuoteconversationFactory $conversationFactory,
        Data $pricingHelper,
        QuoteRepositoryInterface $quoteRepository,
        \Motus\QuoteSystem\Helper\Data $helper,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_customerModel = $customerModel;
        $this->_quoteConversationCollectionFactory = $conversationCollectionFactory;
        $this->_pricingHelper = $pricingHelper;
        $this->_quotesFactory = $_quotesFactory;
        $this->_productFactory = $productFactory;
        $this->_quoteconversationFactory = $conversationFactory;
        $this->_imageHelper = $context->getImageHelper();
        $this->_quoteRepository = $quoteRepository;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getQuoteConversationCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager ::class,
                'quotesystem.pager'
            )
                ->setCollection(
                    $this->getQuoteConversationCollection()
                );
            $this->setChild('pager', $pager);
            $this->getQuoteConversationCollection()->load();
        }

        return $this;
    }
    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    /**
     * customer Id by customer session.
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }
    /**
     * customer data by customer id.
     *
     * @return object
     */
    public function getCustomerData($customerId)
    {
        return $this->_customerModel->load($customerId);
    }
    /**
     * get Collection of quotes conversation for particular quote id.
     *
     * @return collection
     */
    public function getQuoteConversationCollection()
    {
        if (!$this->_quoteConversationCollection) {
            $quoteId = $this->getRequest()->getParam('id');
            if ($quoteId != 0) {
                $collection = $this->_quoteConversationCollectionFactory
                    ->create()
                    ->addFieldToFilter('quote_id', $quoteId);

                $this->_quoteConversationCollection = $collection;
            }
        }

        return $this->_quoteConversationCollection;
    }
    /**
     * get formatted price by currency.
     *
     * @return format price string
     */
    public function getFormattedPrice($price)
    {
        return $this->_pricingHelper
            ->currency($price, true, false);
    }
    /**
     * get quote load by id
     *
     * @param int $entityId
     */
    public function getQuoteData($entityId)
    {
        $quoteModel = $this->_quoteRepository->getById($entityId);
        return $quoteModel;
    }
    /**
     * get product data by id
     *
     * @param int $productId
     */
    public function getProductData($productId)
    {
        $productModel = $this->_productFactory->create()->load($productId);
        return $productModel;
    }
    /**
     * get imageHelper Object to get image of product
     */
    
    public function imageHelperObj()
    {
        return $this->_imageHelper;
    }
    /**
     * check whether a quote's status is sold or not?
     *
     * @param  int $quoteStatus
     * @return boolean
     */
    public function quoteStatusIsNotSold($quoteStatus)
    {
        if ($quoteStatus!=\Motus\Quotesystem\Model\Quotes::STATUS_SOLD) {
            return true;
        }
        return false;
    }

    public function getParameters()
    {
        return $this->getRequest()->getParams();
    }

    /**
     * Get Helper
     *
     * @return Motus\QuoteSystem\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
