<?php
/**
 * Quote save action admin panel.
 */

namespace Motus\Quotesystem\Controller\Adminhtml\Managequotes;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\App\Action;
use Magento\Store\Model\StoreManagerInterface;
use Motus\Quotesystem;
use Magento\Backend\Model\Session;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var storeManager
     */
    protected $_storeManager;

    /**
     * @var Motus\Quotesystem\Helper\Data
     */
    protected $_quotehelper;

    /**
     * @var Motus\Quotesystem\Helper\Mail
     */
    protected $_quoteMailHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var Motus\Quotesystem\Api\QuoteRepositoryInterface
     */
    protected $_quoteRepository;
    
    /**
     * @var TimezoneInterface
     */
    protected $_timezoneinterface;

    /**
     * @param Action\Context                              $context
     * @param StoreManagerInterface                       $storeManager
     * @param Quotesystem\Helper\Data                     $quoteHelper
     * @param Quotesystem\Helper\Mail                     $mailHelper
     * @param \Magento\Framework\Escaper                  $escaper
     * @param Quotesystem\Model\QuoteconversationFactory  $quoteConversationFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param Quotesystem\Api\QuoteRepositoryInterface    $quoteRepository
     * @param TimezoneInterface                           $timezoneinterface
     */
    public function __construct(
        Action\Context $context,
        StoreManagerInterface $storeManager,
        Quotesystem\Helper\Data $quoteHelper,
        Quotesystem\Helper\Mail $mailHelper,
        \Magento\Framework\Escaper $escaper,
        Quotesystem\Model\QuoteconversationFactory $quoteConversationFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Quotesystem\Api\QuoteRepositoryInterface $quoteRepository,
        TimezoneInterface $timezoneinterface
    ) {
        $this->_storeManager = $storeManager;
        $this->_quoteHelper = $quoteHelper;
        $this->_quoteMailHelper = $mailHelper;
        $this->_quoteconversationFactory = $quoteConversationFactory;
        $this->_date = $date;
        $this->_timezoneinterface = $timezoneinterface;
        $this->_quoteRepository = $quoteRepository;
        $this->_escaper=$escaper;
        parent::__construct(
            $context
        );
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Motus_Quotesystem::quotes'
        );
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        $quoteId = 0;
        if (array_key_exists('id', $data)) {
            $quoteId = $data['id'];
        }
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $resultRedirect->setPath(
                        '*/*/index',
                        ['_secure'=>$this->getRequest()->isSecure()]
                    );
                }
                $quote = $this->_quoteRepository->getById($quoteId);
                if ($quote->getEntityId()) {
                    $this->checkAndUpdateData($data, $quote);
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/index',
                        ['_secure'=>$this->getRequest()->isSecure()]
                    );
                } else {
                    $this->messageManager->addError(
                        __('Quote is not exists.')
                    );
                    $quoteId = 0;
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath(
            '*/*/index',
            ['_secure'=>$this->getRequest()->isSecure()]
        );
    }

    public function updateQuoteData($data, $quote)
    {
        $statusFlag = 0;
        $qtyStatus = 0;
        $priceStatus = 0;
        $timezone = $this->_timezoneinterface;
        $date = $this->converToTz(
            $this->_date->gmtDate(),
            // get default timezone of system (UTC)
            $timezone->getDefaultTimezone(),
            // get Config Timezone of current user
            $timezone->getConfigTimezone()
        );
        $attachments = '';
        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $attachments = implode(',', $data['attachments']);
        }
        $quoteConversation = $this->_quoteconversationFactory->create();
        $quoteConversation->setQuoteId($data['quote_id'])
            ->setConversation($this->_escaper->escapeHtml($data['quote_message']))
            ->setSender(0)
            ->setReceiver($quote->getCustomerId())
            ->setAttachments($attachments)
            ->setCreatedAt($date);
        $quoteConversation->save();
        $approvedStatus = \Motus\Quotesystem\Model\Quotes::STATUS_APPROVED;
        $soldStatus = \Motus\Quotesystem\Model\Quotes::STATUS_SOLD;
        if ($quote->getStatus() != $soldStatus) {
            if ($quote->getQuoteQty() != $data['quote_qty']) {
                $quote->setQuoteQty($data['quote_qty']);
                $qtyStatus = 1;
            }
            if ($data['original_price'] != $data['quote_price']) {
                $priceStatus = 1;
                $quote->setQuotePrice(
                    $this->_quoteHelper->getCurrentCurrencyPrice($data['quote_price'], $quote->getQuoteCurrency())
                );
            }
            $product = $this->_quoteRepository->getProductByQuoteId($data['quote_id']);
            if ($quote->getStatus() != $data['status']) {
                $quote->setStatus($data['status'])
                    ->save();
                $statusFlag = 1;
                if ($qtyStatus == 1 || $priceStatus == 1) {
                    $this->_quoteMailHelper->quoteEditedByAdmin(
                        $data['quote_id'],
                        $data['quote_message'],
                        $product
                    );
                } else {
                    $this->_quoteMailHelper->quoteStatusMail(
                        $data['quote_id'],
                        $data['quote_message'],
                        $product
                    );
                }
            } else {
                if ($qtyStatus != 1 && $priceStatus !=1) {
                    $this->_quoteMailHelper->quoteMessage(
                        $data['quote_id'],
                        $data['quote_message'],
                        'admin',
                        $product
                    );
                }
            }
            if ($statusFlag == 0 && ($qtyStatus == 1 || $priceStatus == 1)) {
                $quote->save();
                $this->_quoteMailHelper->quoteEditedByAdmin(
                    $data['quote_id'],
                    $data['quote_message'],
                    $product
                );
            }
            $this->messageManager->addSuccess(
                __('Quote successfully Updated')
            );
        } else {
            if ($quote->getStatus() != $data['status']) {
                if ($quote->getStatus()==\Motus\Quotesystem\Model\Quotes::STATUS_SOLD) {
                    $this->messageManager->addError(
                        __('Quote Already has been sold')
                    );
                }
            } else {
                $this->messageManager->addSuccess(
                    __('Quote successfully Updated')
                );
            }
        }
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
    protected function getProductQty($wholedata, $quote, $product)
    {
        if ($product->getTypeId() == "bundle") {
            $bundleOption = $this->_quoteHelper->convertStringAccToVersion($quote->getBundleOption(), 'decode');
            $validateQty = $this->_quoteHelper->validateBundleProductQuantity(
                $product,
                $bundleOption,
                $quote,
                $wholedata
            );
            $productQty = 0;
            if ($validateQty) {
                $productQty = $this->_quoteHelper->getBundleProductQuatity(
                    $product,
                    $bundleOption
                );
            }
        } elseif ($product->getTypeId()=='configurable') {
            $productQty = $this->_quoteHelper->getConfigurableProductQuantity(
                $product,
                $quote
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

    public function checkAndUpdateData($data, $quote)
    {
        if ($data["status"] == \Motus\Quotesystem\Model\Quotes::STATUS_SOLD
            || $quote->getStatus()== \Motus\Quotesystem\Model\Quotes::STATUS_SOLD
        ) {
            $this->messageManager->addError(
                __(
                    "Not Allowed to update."
                )
            );
            return false;
        }
        $product = $this->_quoteHelper->getProduct($quote->getProductId());
        $productQty = $this->getProductQty($data, $quote, $product);
        if ($product->getMinQuoteQty() > $data['quote_qty']) {
            $this->messageManager->addError(
                __(
                    'Sorry!! Quote Quantity should not be less than %1.',
                    $product->getMinQuoteQty()
                )
            );
            return false;
        }
        if ($data['status'] == \Motus\Quotesystem\Model\Quotes::STATUS_APPROVED &&
        isset($data['quote_qty']) && $productQty < $data['quote_qty']) {
            $this->messageManager->addError(
                __(
                    "Sorry!! This much product quantity is not available"
                )
            );
            return false;
        }
        
        $finalPrice = $this->getFinalProductPrice($quote, $product);
        if (array_key_exists('quote_message', $data)) {
            $this->updateQuoteData($data, $quote);
        }
    }
}
