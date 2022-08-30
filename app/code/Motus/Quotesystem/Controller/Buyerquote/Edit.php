<?php
/**
 * When customer edit a Quote, quote action
 */

namespace Motus\Quotesystem\Controller\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Motus\Quotesystem\Model\QuotesFactory;
use Motus\Quotesystem\Api\QuoteRepositoryInterface;
use Magento\Customer\Model\Url;
use Motus\Quotesystem\Helper\Data as Helper;

class Edit extends \Magento\Customer\Controller\AbstractAccount
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
      * @var QuotesFactory
      */
    protected $_quotesFactory;
    /**
     * @var Motus\Quotesystem\Api\QuoteRepositoryInterface
     */
    protected $_quoteRepository;
    /**
     * @var Magento\Customer\Model\Url
     */
    protected $_customerUrl;
    protected $_helper;
    
    /**
     * @param Context                         $context
     * @param PageFactory                     $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param QuotesFactory                   $quotesFactory
     * @param QuoteRepositoryInterface        $quoteRepository
     * @param Url                             $customerModelUrl
     */

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        QuotesFactory $quotesFactory,
        QuoteRepositoryInterface $quoteRepository,
        Url $customerModelUrl,
        Helper $helper
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_quotesFactory = $quotesFactory;
        $this->_quoteRepository = $quoteRepository;
        $this->_customerUrl = $customerModelUrl;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getConfigValue()) {
            $entityId = $this->getRequest()->getParam('id', false);
            $rightCustomer = $this->isRightCustomer();
            if ($rightCustomer) {
                try {
                    if ($entityId) {
                        $quoteModel = $this->_quoteRepository->getById($entityId);
                        if ($quoteModel->getEntityId()) {
                            /**
 * @var \Magento\Framework\View\Result\Page $resultPage
*/
                            $resultPage = $this->_resultPageFactory->create();
                            $resultPage->getConfig()->getTitle()->set(__('Edit Quote'));
                            return $resultPage;
                        } else {
                            $this->messageManager->addError(__('Quote Does Not exists.'));
                        }
                    } else {
                        $this->messageManager->addError(__('Quote Does Not exists.'));
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, $e->getMessage());
                }
            }
            return $this->resultRedirectFactory
                ->create()->setPath(
                    '*/*/index',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
        } else {
            $this->messageManager->addError(__("Sorry this page is not available"));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setRefererOrBaseUrl();
            return $resultRedirect;
        }
    }
    
    public function isRightCustomer()
    {
        $customerId = $this->_customerSession->getCustomer()->getId();
        $rightCustomer = $this->_quotesFactory
            ->create()
            ->load($this->getRequest()->getParam('id'))
            ->getCustomerId();
        if ($customerId == $rightCustomer) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * get Configuration Value
     *
     * @return $values
     */
    public function getConfigValue()
    {
        return $this->_helper->getConfigValues();
    }
}
