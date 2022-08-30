<?php
/**
 * Quote index action admin panel.
 */

namespace Motus\Quotesystem\Controller\Adminhtml\Managequotes;

use Motus\Quotesystem\Controller\Adminhtml\Managequotes as Managequotes;
use Magento\Framework\Controller\ResultFactory;

class Index extends Managequotes
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Motus_Quotesystem::quotes');
        $resultPage->getConfig()->getTitle()->prepend(
            __('Quote Manager')
        );
        $resultPage->addBreadcrumb(
            __('Quote Manager'),
            __('Quote Manager')
        );
        return $resultPage;
    }
}
