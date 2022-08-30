<?php
/**
 * Update quote action from customer
 */

namespace Motus\Quotesystem\Controller\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Motus\Quotesystem\Model\QuotesFactory;
use Motus\Quotesystem\Helper;
use Magento\Catalog\Model\ProductFactory;
use Motus\Quotesystem\Model\QuoteconversationFactory;
use Magento\Customer\Model\Url;
use Motus\Quotesystem\Api\QuoteRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Updatequote extends \Magento\Customer\Controller\AbstractAccount
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
     * @var \Motus\Quotesystem\Model\Quotes
     */
    protected $_quoteFactory;

    /**
     * @var \Motus\Quotesystem\Helper\Mail
     */
    protected $_mailHelper;

    /**
     * @var QuotesFactory
     */
    protected $_quotesFactory;

    /**
     * @var QuoteconversationFactory
     */
    protected $_quoteconversationFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * @var Motus\Quotesystem\Api\QuoteRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @var Motus\Quotesystem\Helper\Data
     */
    protected $_quoteHelper;

    /**
     * @var TimezoneInterface
     */
    protected $_timezoneinterface;

    /**
     * @param Context                                     $context
     * @param \Magento\Catalog\Model\ProductFactory       $catalogProduct
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Motus\Quotesystem\Model\QuotesFactory     $quotes
     * @param QuoteconversationFactory                    $quoteConversationFactory
     * @param Helper\Mail                                 $helperMail
     * @param Url                                         $customerModelUrl
     * @param QuoteRepositoryInterface                    $quoteRepository
     * @param Helper\Data                                 $quoteHelper
     * @param TimezoneInterface                           $timezoneinterface
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ProductFactory $catalogProduct,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\Session $customerSession,
        \Motus\Quotesystem\Model\QuotesFactory $quotes,
        QuoteconversationFactory $quoteConversationFactory,
        Helper\Mail $helperMail,
        Url $customerModelUrl,
        QuoteRepositoryInterface $quoteRepository,
        Helper\Data $quoteHelper,
        TimezoneInterface $timezoneinterface
    ) {
        $this->_customerSession = $customerSession;
        $this->_catalogProduct = $catalogProduct;
        $this->_date = $date;
        $this->_quoteFactory = $quotes;
        $this->_mailHelper = $helperMail;
        $this->_quoteconversationFactory = $quoteConversationFactory;
        $this->_customerUrl = $customerModelUrl;
        $this->_quoteRepository = $quoteRepository;
        $this->_quoteHelper = $quoteHelper;
        $this->_timezoneinterface = $timezoneinterface;
        parent::__construct($context);
    }

    /**
     * Save quote from buyer.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory
                ->create()->setPath(
                    'quotesystem/buyerquote/index/'
                );
        }

        $wholedata = $this->getRequest()->getParams();
        $quoteSuccess = 0;
        $messageSuccess = 0;
        $productQty = $this->getProductQty($wholedata);
        if ($wholedata && array_key_exists('quote_id', $wholedata)) {
            $quote = $this->_quoteRepository->getById($wholedata['quote_id']);
            $quoteProduct = $this->_quoteHelper->getProduct($quote->getProductId());
            if (isset($wholedata['quote_update_switch'])) {
                if (isset($wholedata['quote_price']) && isset($wholedata['quote_qty'])) {
                    if ($quoteProduct->getMinQuoteQty() <= $wholedata['quote_qty']) {
                        if ($quote->getStatus() == \Motus\Quotesystem\Model\Quotes::STATUS_UNAPPROVED) {
                            $quotePrice = $this->_quoteHelper->getBaseCurrencyPrice($wholedata['quote_price']);
                            $quote->setQuotePrice($wholedata['quote_price'])
                                ->setQuoteQty($wholedata['quote_qty'])
                                ->save();
                            $quoteSuccess = 1;
                            $this->messageManager->addSuccess(__('Quote Successfully updated'));
                        } else {
                            $this->messageManager->addNotice(__('Sorry!! Quote Status Changed previously'));
                        }
                    } else {
                        $this->messageManager->addError(
                            __(
                                'Sorry!! Quote Quantity should not be less than %1.',
                                $wholedata['minquote_quantity']
                            )
                        );
                    }
                }
            }
            if (isset($wholedata['quote_message'])) {
                $timezone = $this->_timezoneinterface;
                $date = $this->converToTz(
                    $this->_date->gmtDate(),
                    // get default timezone of system (UTC)
                    $timezone->getDefaultTimezone(),
                    // get Config Timezone of current user
                    $timezone->getConfigTimezone()
                );

                $attachments = '';
                if (isset($wholedata['attachments']) && is_array($wholedata['attachments'])) {
                    $attachments = implode(',', $wholedata['attachments']);
                }
                $customerId = $this->_customerSession->getCustomerId();
                $quoteConversation = $this->_quoteconversationFactory->create()
                    ->setSender($customerId)
                    ->setReceiver(0)
                    ->setConversation($wholedata['quote_message'])
                    ->setQuoteId($wholedata['quote_id'])
                    ->setCreatedAt($date)
                    ->setAttachments($attachments)
                    ->save();
                $messageSuccess = 1;
                $this->messageManager->addSuccess(__('Message Sent Successfully'));
            }
            if ($quoteSuccess == 1 && $messageSuccess == 1) {
                $this->_mailHelper->quoteEdited(
                    $wholedata['quote_id'],
                    $wholedata['quote_message']
                );
            } elseif ($messageSuccess == 1) {
                $this->_mailHelper->quoteMessage(
                    $wholedata['quote_id'],
                    $wholedata['quote_message'],
                    'customer',
                    $quoteProduct
                );
            }
            return $this->resultRedirectFactory
                ->create()->setPath(
                    'quotesystem/buyerquote/edit/',
                    [
                    'id' => $wholedata['quote_id'],
                    '_secure' => $this->getRequest()->isSecure(),
                    ]
                );
        } else {
            $this->messageManager->addError(__('Something Went Wrong, Please try again later.'));
            return $this->resultRedirectFactory
                ->create()->setPath(
                    'quotesystem/buyerquote/index/',
                    [
                        '_secure' => $this->getRequest()->isSecure()
                    ]
                );
        }
        return $this->resultRedirectFactory
            ->create()->setPath(
                'quotesystem/buyerquote/index/',
                [
                    '_secure' => $this->getRequest()->isSecure()
                ]
            );
    }

    protected function converToTz($dateTime = "", $toTz = '', $fromTz = '')
    {
        // timezone by php friendly values
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('m/d/Y H:i:s');
        return $dateTime;
    }

    /**
     * [getProductQty description]
     *
     * @param  $productId
     * @return $productQty
     */
    protected function getProductQty($wholedata)
    {
        $quoteProduct = $this->_quoteFactory->create()->load($wholedata['quote_id']);
        $productId = $wholedata['product_id'];
        $product = $this->_catalogProduct->create()->load($productId);
        if ($product->getTypeId() == "bundle") {
            $bundleOption = $this->_quoteHelper->convertStringAccToVersion($quoteProduct->getBundleOption(), 'decode');
            $productQty = $this->_quoteHelper->getBundleProductQuatity(
                $product,
                $bundleOption
            );
        } elseif ($product->getTypeId()=='configurable') {
            $productQty = $this->_quoteHelper->getConfigurableProductQuantity(
                $product,
                $quoteProduct
            );
        } else {
            $productQty = $product->getQuantityAndStockStatus()['qty'];
        }
        return round($productQty);
    }
    
    public function getFinalProductPrice($quote, $product)
    {
        $params = [];
        if ($product->getTypeId()=='bundle') {
            $bundleOption = $this->_quoteHelper->convertStringAccToVersion($quote->getBundleOption(), 'decode');
            $params['bundle_option_to_calculate'] = $bundleOption;
        }
        $params['options'] = $this->_quoteHelper->convertStringAccToVersion($quote->getProductOption(), 'decode');
        $params['super_attribute'] = $this->_quoteHelper->convertStringAccToVersion(
            $quote->getSuperAttribute(),
            'decode'
        );
        $params['links'] = $this->_quoteHelper->convertStringAccToVersion($quote->getLinks(), 'decode');
        $params['product'] = $product->getEntityId();
        $productPrice = $this->_quoteHelper->calculateProductPrice(
            $params
        );
        return($productPrice);
    }
}
