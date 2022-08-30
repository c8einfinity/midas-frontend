<?php
/**
 * Quote Mass update action admin.
 */

namespace Motus\Quotesystem\Controller\Adminhtml\Managequotes;

use Magento\Backend\App\Action;
use Motus\Quotesystem;
use Magento\Ui\Component\MassAction\Filter;

class MassUpdate extends \Magento\Backend\App\Action
{
    /**
     * @var Motus\Quotesystem\Helper\Data
     */
    protected $_quoteHelper;
    
    /**
     * @var Motus\Quotesystem\Api\QuoteRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var Quotesystem\Model\ResourceModel\Quotes\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var Motus\Quotesystem\Helper\Mail
     */
    protected $_quoteMailHelper;
    
    /**
     * @param Action\Context                                           $context
     * @param Quotesystem\Helper\Data                                  $quoteHelper
     * @param Filter                                                   $filter
     * @param Quotesystem\Api\QuoteRepositoryInterface                 $quoteRepository
     * @param Quotesystem\Model\ResourceModel\Quotes\CollectionFactory $collectionFactory
     * @param Quotesystem\Helper\Mail                                  $mailHelper
     */
    public function __construct(
        Action\Context $context,
        Quotesystem\Helper\Data $quoteHelper,
        Filter $filter,
        Quotesystem\Api\QuoteRepositoryInterface $quoteRepository,
        Quotesystem\Model\ResourceModel\Quotes\CollectionFactory $collectionFactory,
        Quotesystem\Helper\Mail $mailHelper
    ) {
        parent::__construct($context);
        $this->_quoteHelper = $quoteHelper;
        $this->_quoteRepository = $quoteRepository;
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_quoteMailHelper = $mailHelper;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Motus_Quotesystem::quotes'
        );
    }

    /**
     * Mass Update action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $resultRedirect = $this->resultRedirectFactory->create();
            $data = $this->getRequest()->getParams();
            $status = $data['quoteupdate'];
            if ($status == \Motus\Quotesystem\Model\Quotes::STATUS_SOLD) {
                $this->messageManager->addError(
                    __(
                        'Can not update Quote(s) Status to sold.'
                    )
                );
                return $resultRedirect->setPath('*/*/');
            }
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $quoteIds = $collection->getAllIds();
            list($updatedQuoteIds, $error) = $this->_validateMassUpdate($quoteIds, $status);
            if (!empty($error)) {
                foreach ($error as $message) {
                    $this->messageManager->addError($message);
                }
            }
            if (count($updatedQuoteIds)) {
                $coditionArr = [];
                foreach ($updatedQuoteIds as $key => $id) {
                    $condition = "`entity_id`=".$id;
                    array_push($coditionArr, $condition);
                }
                $coditionData = implode(' OR ', $coditionArr);

                $quotesCollection = $this->_collectionFactory->create();
                $quotesCollection->setTableRecords(
                    $coditionData,
                    ['status' => $status]
                );
                if ($status == 3) {
                    $this->_quoteHelper->removeCartItem($updatedQuoteIds);
                }
                foreach ($updatedQuoteIds as $quoteId) {
                    $product = $this->_quoteRepository->getProductByQuoteId($quoteId);
                    $this->_quoteMailHelper->quoteStatusMail(
                        $quoteId,
                        __('Quote Status is updated by admin.'),
                        $product
                    );
                }
                $this->messageManager->addSuccess(
                    __(
                        'A Total of %1 record(s) successfully updated.',
                        count($updatedQuoteIds)
                    )
                );
            }
            return $resultRedirect->setPath('*/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while Updating the data.')
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    public function _validateMassUpdate($quoteIds, $status)
    {
        $error = [];
        if (count($quoteIds)) {
            foreach ($quoteIds as $key => $quoteId) {
                $quote = $this->_quoteRepository->getById($quoteId);
                $product = $this->_quoteHelper->getProduct($quote->getProductId());
                $productQty = $this->getProductQty($quote, $product);
                if ($quote->getStatus() == \Motus\Quotesystem\Model\Quotes::STATUS_SOLD) {
                    $error[] = __('Quote id %1 Already has been sold', $quoteId);
                    unset($quoteIds[$key]);
                } elseif ($status==\Motus\Quotesystem\Model\Quotes::STATUS_UNAPPROVED
                    && ($quote->getStatus()==\Motus\Quotesystem\Model\Quotes::STATUS_DECLINE
                    || $quote->getStatus()==\Motus\Quotesystem\Model\Quotes::STATUS_APPROVED)
                ) {
                    $error[] = __('Can not update status for quote id %1', $quoteId);
                    unset($quoteIds[$key]);
                } elseif ($status == \Motus\Quotesystem\Model\Quotes::STATUS_APPROVED
                && $productQty < $quote->getQuoteQty() &&
                $status == \Motus\Quotesystem\Model\Quotes::STATUS_APPROVED) {
                    unset($quoteIds[$key]);
                    $error[] = __('Requested quantity is not available for quote id %1.', $quoteId);
                }
            }
        }
        return [$quoteIds,$error];
    }

    protected function getProductQty($quote, $product)
    {
        if ($product->getTypeId() == "bundle") {
            $bundleOption = $this->_quoteHelper->convertStringAccToVersion($quote->getBundleOption(), 'decode');
            $validateQty = $this->_quoteHelper->validateBundleProductQuantity(
                $product,
                $bundleOption,
                $quote,
                []
            );
            $productQty = 0;
            if ($validateQty) {
                $productQty = $this->_quoteHelper->getBundleProductQuatity(
                    $product,
                    $bundleOption
                );
            }
        } elseif ($product->getTypeId()=='downloadable') {
            $productQty = $quote->getQuoteQty();
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
}
