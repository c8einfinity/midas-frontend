<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_PartFinder
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\PartFinder\Controller\Adminhtml\Partfinder;

use Webkul\PartFinder\Controller\Adminhtml\Partfinder;
use Magento\Backend\App\Action\Context;
use Webkul\PartFinder\Model\PartfinderFactory;

class Delete extends \Webkul\PartFinder\Controller\Adminhtml\Partfinder
{
    /**
     * @var \Webkul\PartFinder\Model\PartfinderFactory
     */
    protected $partfinderFactory;

    /**
     * @param Context $context
     * @param PartfinderFactory $partfinderFactory
     */
    public function __construct(
        Context $context,
        PartfinderFactory $partfinderFactory
    ) {
        parent::__construct($context);
        $this->partfinderFactory = $partfinderFactory;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $partFinderId = $this->getRequest()->getParam('entity_id');
        if ($partFinderId) {
            try {
                $partfinder = $this->partfinderFactory->create()->load($partFinderId);
                $partfinder->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the Partfinder.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $partFinderId]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a Partfinder to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
