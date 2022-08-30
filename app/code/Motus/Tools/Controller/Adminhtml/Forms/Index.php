<?php


namespace Motus\Tools\Controller\Adminhtml\Forms;


use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Index extends \Magento\Backend\App\Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    protected $resultPageFactory;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;

    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Motus_Tools::forms');
        $resultPage->getConfig()->getTitle()->prepend(__('Motus Forms'));
        return $resultPage;
    }

    public function createCsrfValidationException( RequestInterface $request ): ?       InvalidRequestException {
        return null;
    }

    /** * @inheritDoc */
    public function validateForCsrf(RequestInterface $request): ?bool {
        return true;
    }

}
