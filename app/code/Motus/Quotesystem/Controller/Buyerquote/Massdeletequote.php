<?php
/**
 * Mass Delete Quotes at Customer end.
 */

namespace Motus\Quotesystem\Controller\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Motus\Quotesystem\Api\QuoteRepositoryInterface;

class Massdeletequote extends \Magento\Customer\Controller\AbstractAccount
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
    protected function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * Delete mass quotes from repository.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $ids = $this->getRequest()->getParam('quote_mass_delete', []);
            if (count($ids)) {
                foreach ($ids as $entityId) {
                    $this->_quoteRepository->deleteById($entityId);
                }
                $this->messageManager->addSuccess(__('Quotes are successfully deleted.'));
            } else {
                $this->messageManager->addError(__('Please select checkbox first.'));
            }
            return $this->resultRedirectFactory
                ->create()->setPath(
                    '*/*/view',
                    [
                        'quote_id'=>$this->getCustomerSession()->getSelectedQuoteId(),
                        '_secure'=>$this->getRequest()->isSecure()
                    ]
                );
        } catch (\Exception $e) {
            $this->messageManager->addException($e, $e->getMessage());
            return $this->resultRedirectFactory
                ->create()->setPath(
                    '*/*/view',
                    [
                        'quote_id'=>$this->getCustomerSession()->getSelectedQuoteId(),
                        '_secure'=>$this->getRequest()->isSecure()
                    ]
                );
        }
    }
}
