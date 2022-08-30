<?php
/**
 * All Quotes page at customer end.
 */

namespace Motus\Quotesystem\Controller\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Url;
use Motus\Quotesystem\Helper\Data as Helper;

class Index extends \Magento\Customer\Controller\AbstractAccount
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
     * @var customerUrl
     */
    protected $_customerUrl;
    protected $_helper;
    /**
     * @param Context                         $context
     * @param PageFactory                     $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param url                             $customerUrl
     */

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        url $customerUrl,
        Helper $helper
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_customerUrl = $customerUrl;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    /**
     * Buyer Manage Quote Page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getConfigValue()) {
            /**
 * @var \Magento\Framework\View\Result\Page $resultPage
*/
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('My Quotes'));
            return $resultPage;
        } else {
            $this->messageManager->addError(__("Sorry this page is not available"));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setRefererOrBaseUrl();
            return $resultRedirect;
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
