<?php
/**
 * Quote Edit controller, admin panel.
 */

namespace Motus\Quotesystem\Controller\Adminhtml\ui;

use Magento\Backend\App\Action;

class Form extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Action\Context                             $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->backendSession = $backendSession;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $params = $this->_request->getParams();
        if (isset($params['sort']) || isset($params['filter'])) {
            $this->backendSession->setIsSort(true);
        } else {
            $this->backendSession->setIsSort(false);
        }
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Add Quote'));
        return $resultPage;
    }
    
     /**
      * Check for is allowed
      *
      * @return boolean
      */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Motus_Quotesystem::quotes');
    }
}
