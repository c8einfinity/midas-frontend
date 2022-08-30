<?php
/**
 * Block for edit quote at customer end.
 */

namespace Motus\Quotesystem\Block;

use Motus\Quotesystem\Model\ResourceModel\Quoteconversation;
use Magento\Framework\Pricing\Helper\Data;
use Motus\Quotesystem\Model\QuotesFactory;
use Motus\Quotesystem\Model\QuoteconversationFactory;
use Magento\Catalog\Model\ProductFactory;
use Motus\Quotesystem\Api\QuoteRepositoryInterface;
use Motus\Quotesystem\Block\Quoteproduct;

class Editquotes extends \Magento\Framework\View\Element\Template
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
     * @var _quoteConversationCollection
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
     * @var _quoteConvCollectionFactory
     */
    protected $_quoteConvCollectionFactory;
    /**
     * @var _quoteconversationFactory
     */
    protected $_quoteconversationFactory;
    /**
     * @var Motus\Quotesystem\Api\QuoteRepositoryInterface
     */
    protected $_quoteRepository;
    protected $_timezone;

    protected $_stockItemRepository;

    /**
     * @param \Magento\Customer\Model\Session        $customerSession
     * @param \Magento\Customer\Model\Customer       $customerModel
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param Quoteconversation\CollectionFactory    $convtionCollectionFactory
     * @param QuotesFactory                          $_quotesFactory
     * @param ProductFactory                         $productFactory
     * @param QuoteconversationFactory               $conversationFactory
     * @param Data                                   $pricingHelper
     * @param QuoteRepositoryInterface               $quoteRepository
     * @param array                                  $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        Quoteconversation\CollectionFactory $convtionCollectionFactory,
        QuotesFactory $_quotesFactory,
        ProductFactory $productFactory,
        QuoteconversationFactory $conversationFactory,
        Data $pricingHelper,
        QuoteRepositoryInterface $quoteRepository,
        \Motus\Quotesystem\Helper\Data $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_customerModel = $customerModel;
        $this->_quoteConvCollectionFactory = $convtionCollectionFactory;
        $this->_pricingHelper = $pricingHelper;
        $this->_quotesFactory = $_quotesFactory;
        $this->_productFactory = $productFactory;
        $this->_quoteconversationFactory = $conversationFactory;
        $this->_imageHelper = $context->getImageHelper();
        $this->_quoteRepository = $quoteRepository;
        $this->_timezone = $context->getLocaleDate();
        $this->_stockItemRepository = $stockItemRepository;
        $this->_helper = $helper;
        $this->jsonHelper = $jsonHelper;
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
                $collection = $this->_quoteConvCollectionFactory
                    ->create()
                    ->addFieldToFilter('quote_id', $quoteId)
                    ->setOrder('created_at', 'DESC');

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
     * get quote data by quote id
     *
     * @param int $entityId
     */
    public function getQuoteData($entityId)
    {
        $quoteModel = $this->_quoteRepository->getById($entityId);
        return $quoteModel;
    }

    /**
     * get product data by product id
     *
     * @param int $productId
     */
    public function getProductData($productId)
    {
        $productModel = $this->_productFactory->create()->load($productId);
        return $productModel;
    }

    /**
     * return image helper object to get product images
     */
    public function imageHelperObj()
    {
        return $this->_imageHelper;
    }

    /**
     * check quote status whether a quote is sold or not?
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
    public function getFormattedTime($time)
    {
        return $this->_timezone->date(new \DateTime($time))->format('Y-m-d H:i:s');
    }
    public function getProductQty($productId)
    {
        return $this->_stockItemRepository->get($productId)->getQty();
    }

    public function getMinQuoteQuatity($productId)
    {
        $productModel = $this->getProductData($productId);
        return $productModel->getMinQuoteQty();
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
