<?php
/**
 * Block for Quote list at customer end.
 */

namespace Motus\Quotesystem\Block;

use Motus\Quotesystem\Model\ResourceModel\QuoteDetails\CollectionFactory;
use Magento\Framework\Pricing\Helper\Data;
use Motus\Quotesystem\Model\Quotes;
use Motus\Quotesystem\Helper\Data as Helper;

class QuoteDetails extends \Magento\Framework\View\Element\Template
{
    /**
     * @var customerSession
     */
    protected $_customerSession;
    /**
     * @var quoteCollectionFacory
     */
    protected $_quoteCollectionFactory;
    /**
     * @var quoteCollection
     */
    protected $_quoteCollection;
    /**
     * @var pricingHelper
     */
    protected $_pricingHelper;
    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $_urlEncoder;
    /**
     * @var Motus\Quoetsystem\Model\Quotes
     */
    protected $_quotesModel;

    /**
     * @var $quoteId
     */
    public $quoteId = "fa fa-sort";

    /**
     * @var $quantityClass
     */
    public $createdAt = "fa fa-sort";

    protected $helper;

    protected $storeManager;

    /**
     * [__construct description]
     *
     * @param MagentoCustomerModelSession         $customerSession
     * @param MagentoCatalogBlockProductContext   $context
     * @param CollectionFactory                   $quotesCollectionFactory
     * @param Data                                $pricingHelper
     * @param MagentoFrameworkUrlEncoderInterface $urlEncoder
     * @param Quotes                              $quotesModel
     * @param [type]                              $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Block\Product\Context $context,
        CollectionFactory $quotesCollectionFactory,
        Data $pricingHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        Quotes $quotesModel,
        Helper $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Motus\Quotesystem\Model\QuotesFactory $quotes,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_quoteCollectionFactory = $quotesCollectionFactory;
        $this->_pricingHelper = $pricingHelper;
        $this->_urlEncoder = $urlEncoder;
        $this->_quotesModel = $quotesModel;
        $this->_storeManager =  $context->getStoreManager();
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        $this->quotes = $quotes;
        parent::__construct($context, $data);
    }
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getQuotesCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager ::class,
                'quotesystem.pager'
            )
                ->setCollection(
                    $this->getQuotesCollection()
                );
            $this->setChild('pager', $pager);
            $this->getQuotesCollection()->load();
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
     * customer Id by customer session
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }
    /**
     * get Collection of quotes
     *
     * @return collection
     */
    public function getQuotesCollection()
    {
        $filter = '';
        $filterStatus = 0;
        $filterSorting = '';
        $data = $this->getRequest()->getParams();
        if ($id = $this->getRequest()->getParam('id')) {
            $filter = $id;
        }
        if (isset($data['status'])) {
            $filterStatus = $data['status'] != "" ? $data['status'] : 0;
        }
        if (isset($data['sortingStatus'])) {
            $filterSorting = $data['sortingStatus'];
        }
        if (!$this->_quoteCollection) {
            $collection = $this->_quoteCollectionFactory
                ->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter(
                    'main_table.customer_id',
                    $this->getCustomerId()
                );
            if (!empty($filter)) {
                $collection->addFieldToFilter('entity_id', $filter);
            }
            $collection->addFieldToFilter('quote_generate', 1);
            $collection->getSelect()
                ->join(
                    ["css" => $collection->getTable("mot_quotes")],
                    "main_table.entity_id = css.quote_id"
                );
            $collection->getSelect()->group("main_table.entity_id");
            $collection = $this->getCollectionBySortingOrder($collection, $filterSorting);
            $this->getClassNameBySortOrder($filterSorting);
            $this->_quoteCollection = $collection;
        }

        return $this->_quoteCollection;
    }

    /**
     * getClassNameBySortOrder
     *
     * @param $filterSorting
     */
    protected function getClassNameBySortOrder($filterSorting)
    {
        if ($filterSorting == 'createdAtInc') {
            $this->createdAt = "fa fa-sort-asc";
        } elseif ($filterSorting == 'createdAtDesc') {
            $this->createdAt = "fa fa-sort-desc";
        } elseif ($filterSorting == 'quoteIdInc') {
            $this->quoteId = "fa fa-sort-asc";
        } elseif ($filterSorting == 'quoteIdDesc') {
            $this->quoteId = "fa fa-sort-desc";
        }
    }

    /**
     * getCollectionBySortingOrder
     *
     * @param  $collection
     * @param  $filterSorting
     * @return $collection
     */
    protected function getCollectionBySortingOrder($collection, $filterSorting)
    {
        if ($filterSorting == 'quoteIdInc') {
            $collection = $collection->setOrder('main_table.entity_id', 'ASC');
            return $collection;
        } elseif ($filterSorting == 'quoteIdDesc') {
            $collection = $collection->setOrder('main_table.entity_id', 'DESC');
            return $collection;
        } elseif ($filterSorting == 'createdAtInc') {
            $collection = $collection->setOrder('main_table.created_at', 'ASC');
            return $collection;
        } elseif ($filterSorting == 'createdAtDesc') {
            $collection = $collection->setOrder('main_table.created_at', 'DESC');
            return $collection;
        } else {
            $collection = $collection->setOrder('main_table.created_at', 'ASC');
            return $collection;
        }
    }

    /**
     * check whether a quote is sold or not?
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

    /**
     * getIsSecure
     */
    public function getIsSecure()
    {
        return $this->getRequest()->isSecure();
    }

    /**
     * get Configuration Value
     *
     * @return $values
     */
    public function getConfigValue()
    {
        return $this->helper->getConfigValues();
    }

    public function getBaseUrl()
    {
        return $this->getBaseUrl();
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

    /**
     * Get Json Helper
     *
     * @return Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    /**
     * get Total Price of quote
     *
     * @param int $quoteId
     * @return int
     */
    public function getTotalPrice($quoteId)
    {
        $totalQuotePrice = 0;
        $quotesCollection = $this->quotes->create()
                                ->getCollection()
                                ->addFieldToFilter('quote_id', $quoteId);
        foreach ($quotesCollection as $quoteData) {
            $totalQuotePrice = $totalQuotePrice + $quoteData->getQuotePrice();
        }
        return $totalQuotePrice;
    }

    /**
     * Get Quote Status at runtime
     *
     * @param int $quoteId
     * @return string
     */
    public function getQuoteStatus($quoteId)
    {
        $status = [];
        $quotesCollection = $this->quotes->create()
                                ->getCollection()
                                ->addFieldToFilter('quote_id', $quoteId);
        foreach ($quotesCollection as $quoteData) {
            $quoteStatus = $quoteData->getStatus();
            array_push($status, $quoteStatus);
        }
        if (!in_array(1, $status) && !in_array(2, $status) && !in_array(3, $status)) {
            $qtStatus = __('Sold');
        } elseif (!in_array(1, $status) && !in_array(2, $status) && !in_array(4, $status)) {
            $qtStatus = __('Canceled');
        } elseif (!in_array(3, $status) && !in_array(2, $status) && !in_array(4, $status)) {
            $qtStatus = __('Pending');
        } else {
            $qtStatus = __('Processing');
        }
        return $qtStatus;
    }
}
