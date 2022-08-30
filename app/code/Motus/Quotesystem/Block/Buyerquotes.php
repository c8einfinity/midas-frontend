<?php
/**
 * Block for Quote list at customer end.
 */

namespace Motus\Quotesystem\Block;

use Motus\Quotesystem\Model\ResourceModel\Quotes\CollectionFactory;
use Magento\Framework\Pricing\Helper\Data;
use Motus\Quotesystem\Model\Quotes;
use Motus\Quotesystem\Helper\Data as Helper;

class Buyerquotes extends \Magento\Framework\View\Element\Template
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
     * @var $nameClass
     */
    public $nameClass = "fa fa-sort";

    /**
     * @var $quantityClass
     */
    public $quantityClass = "fa fa-sort";

    /**
     * @var $priceClass
     */
    public $priceClass = "fa fa-sort";

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
        $attribute = $this->attributeIdOfProductName();
        $filter = '';
        $filterStatus = 0;
        $filterSorting = '';
        $quoteId = $this->_customerSession->getSelectedQuoteId();
        $data = $this->getRequest()->getParams();
        if ($proName = $this->getRequest()->getParam('s')) {
            $filter = $proName != "" ? $proName : "";
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
                    'customer_id',
                    $this->getCustomerId()
                );
            if (!empty($filter)) {
                $collection->addFieldToFilter('product_name', ['like' => '%'.$filter.'%']);
            }
            if ($filterStatus != 0) {
                $collection->addFieldToFilter('status', $filterStatus);
            }
            $collection->addFieldToFilter('quote_id', $quoteId);
            $collection->getSelect()
                ->joinLeft(
                    ["cpev" => $collection->getTable("catalog_product_entity_varchar")],
                    "cpev.entity_id = main_table.product_id AND cpev.attribute_id = ". $attribute."",
                    ["prod_name" => "cpev.value",'pro_id' => '']
                )
                ->joinLeft(
                    ["css" => $collection->getTable("cataloginventory_stock_status")],
                    "css.product_id = main_table.product_id",
                    ["stockstatus" => "css.stock_status"]
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
        if ($filterSorting == 'productNameInc') {
            $this->nameClass = "fa fa-sort-asc";
        } elseif ($filterSorting == 'productNameDesc') {
            $this->nameClass = "fa fa-sort-desc";
        } elseif ($filterSorting == 'productQuantityInc') {
            $this->quantityClass = "fa fa-sort-asc";
        } elseif ($filterSorting == 'productQuantityDesc') {
            $this->quantityClass = "fa fa-sort-desc";
        } elseif ($filterSorting == 'productPriceInc') {
            $this->priceClass = "fa fa-sort-asc";
        } elseif ($filterSorting == 'productPriceDesc') {
            $this->priceClass = "fa fa-sort-desc";
        }
    }
    /**
     * get formatted price by currency
     *
     * @return format price string
     */
    public function getFormattedPrice($price)
    {
        $fromCurrency = $this->helper->getCurrentCurrencyCode();
        $toCurrency = $this->helper->getBaseCurrencyCode();
        $amount = $this->helper->getmotconvertCurrency($fromCurrency, $toCurrency, $price);
        return $this->_pricingHelper
            ->currency($amount, true, false);
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
        if ($filterSorting == 'productNameInc') {
            $collection = $collection->setOrder('product_name', 'ASC');
            return $collection;
        } elseif ($filterSorting == 'productNameDesc') {
            $collection = $collection->setOrder('product_name', 'DESC');
            return $collection;
        } elseif ($filterSorting == 'productQuantityInc') {
            $collection = $collection->setOrder('quote_qty', 'ASC');
            return $collection;
        } elseif ($filterSorting == 'productQuantityDesc') {
            $collection = $collection->setOrder('quote_qty', 'DESC');
            return $collection;
        } elseif ($filterSorting == 'productPriceInc') {
            $collection = $collection->setOrder('quote_price', 'ASC');
            return $collection;
        } elseif ($filterSorting == 'productPriceDesc') {
            $collection = $collection->setOrder('quote_price', 'DESC');
            return $collection;
        } else {
             $collection = $collection->setOrder('created_at', 'DESC');
             return $collection;
        }
    }

    protected function attributeIdOfProductName()
    {
        $object = \Magento\Framework\App\ObjectManager::getInstance();
        $attribute = $object->get(\Magento\Eav\Api\AttributeRepositoryInterface ::class);
        $attributeId = $attribute->get('catalog_product', 'name')->getId();
        return $attributeId;
    }

    /**
     * use to get current url.
     */
    public function getCurrentUrl()
    {
        // Give the current url of recently viewed page
        return $this->_urlBuilder->getCurrentUrl();
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

    public function getQuoteId()
    {
        return $this->_customerSession->getSelectedQuoteId();
    }
}
