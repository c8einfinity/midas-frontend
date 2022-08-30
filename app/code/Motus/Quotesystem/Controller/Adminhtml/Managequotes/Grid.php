<?php
/**
 * Quote Grid controller
 */

namespace Motus\Quotesystem\Controller\Adminhtml\Managequotes;

use Magento\Framework\Controller\ResultFactory;

class Grid extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context          $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->_resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('quotesystem.managequotes.edit.tab.conversation');
        return $resultLayout;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Motus_Quotesystem::quotes'
        );
    }
}
