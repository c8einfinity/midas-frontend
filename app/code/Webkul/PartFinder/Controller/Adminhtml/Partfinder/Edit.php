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
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Webkul\PartFinder\Model\PartfinderFactory;

class Edit extends Partfinder
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\PartFinder\Model\PartfinderFactory
     */
    protected $partfinderFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        PartfinderFactory $partfinderFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->partfinderFactory = $partfinderFactory;
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('entity_id');
        $partfinder = $this->partfinderFactory->create();

        // 2. Initial checking
        if ($id) {
            $partfinder->load($id);
            if (!$partfinder->getId()) {
                $this->messageManager->addErrorMessage(__('This Part finder no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('webkul_partfinder', $partfinder);

        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Partfinder') : __('New Part Finder'),
            $id ? __('Edit Partfinder') : __('New Part Finder')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Part Finders'));
        $resultPage->getConfig()->getTitle()->prepend(
            $partfinder->getId() ? $partfinder->getName() : __('New Part Finder')
        );
        return $resultPage;
    }
}
