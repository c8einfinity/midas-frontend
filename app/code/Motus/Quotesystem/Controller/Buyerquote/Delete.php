<?php
/**
 * Quote Delete from buyer end.
 */

namespace Motus\Quotesystem\Controller\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Motus\Quotesystem\Api\QuoteRepositoryInterface;
use Magento\Customer\Model\Url;

class Delete extends \Magento\Customer\Controller\AbstractAccount
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
     * @var Motus\Quotesystem\Api\QuoteRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @param Context                  $context
     * @param Session                  $customerSession
     * @param PageFactory              $resultPageFactory
     * @param QuoteRepositoryInterface $quoteRepository
     * @param Url                      $customerModelUrl
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        QuoteRepositoryInterface $quoteRepository
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_quoteRepository = $quoteRepository;
        parent::__construct(
            $context
        );
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Delete quote from model.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $quoteId = $this->getRequest()->getParam('id', false);
            $customerSessionId = $this->_customerSession->getCustomerId();
            $qouteCustomerId = $this->_quoteRepository->getCustomerIdByQuoteId($quoteId);
            if ($quoteId) {
                if ($customerSessionId == $qouteCustomerId) {
                    $this->_quoteRepository->deleteById($quoteId);
                    $this->messageManager->addSuccess(__('Quote is successfully deleted.'));
                } else {
                    $this->messageManager->addError(__('You are not allowed to delete the quotes of other.'));
                }
            } else {
                $this->messageManager->addError(__('Quote Does Not exists.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, $e->getMessage());
        }
        return $this->resultRedirectFactory
            ->create()->setPath(
                '*/*/index',
                ['_secure'=>$this->getRequest()->isSecure()]
            );
    }
}
