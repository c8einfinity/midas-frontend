<?php
/**
 * Quote mass delete action admin panel.
 */

namespace Motus\Quotesystem\Controller\Adminhtml\Managequotes;

use Magento\Backend\App\Action;
use Motus\Quotesystem;
use Magento\Ui\Component\MassAction\Filter;

class Massdelete extends \Magento\Backend\App\Action
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
     * @param Action\Context                                           $context
     * @param Quotesystem\Helper\Data                                  $quoteHelper
     * @param Filter                                                   $filter
     * @param Quotesystem\Api\QuoteRepositoryInterface                 $quoteRepository
     * @param Quotesystem\Model\ResourceModel\Quotes\CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        Quotesystem\Helper\Data $quoteHelper,
        Filter $filter,
        Quotesystem\Api\QuoteRepositoryInterface $quoteRepository,
        Quotesystem\Model\ResourceModel\Quotes\CollectionFactory $collectionFactory
    ) {
        $this->_quoteHelper = $quoteHelper;
        $this->_quoteRepository = $quoteRepository;
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Motus_Quotesystem::quotes'
        );
    }
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $quoteDeleted = 0;
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        try {
            foreach ($collection as $item) {
                $this->_quoteRepository->deleteById($item->getEntityId());
                $quoteDeleted++;
            }
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been deleted.', $quoteDeleted)
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while Deleting the data.')
            );
        }
        return $resultRedirect->setPath(
            '*/*/index',
            ['_secure'=>$this->getRequest()->isSecure()]
        );
    }
}
