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
namespace Webkul\PartFinder\Controller\Adminhtml\Partfinder\Profile;

use Webkul\PartFinder\Controller\Adminhtml\Partfinder;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Webkul\PartFinder\Api\Data\ProfileDataInterfaceFactory;

class Edit extends \Webkul\PartFinder\Controller\Adminhtml\Partfinder
{
    /**
     * @var \Magento\Framework\Registry;
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\PartFinder\Api\Data\ProfileDataInterfaceFactory
     */
    protected $profileFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param ProfileDataInterfaceFactory $profileFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        ProfileDataInterfaceFactory $profileFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->profileFactory = $profileFactory;
    }

    /**
     * Edit Action
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        /** @var $model \Webkul\PartFinder\Model\ProfileData */
        $model = $this->profileFactory->create();
        
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This profile no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('partfinder/*/');
            }
        }
        $this->coreRegistry->register('profile_data', $model);
        
        $resultPage = $this->createActionPage();
        $resultPage->getConfig()->getTitle()->prepend($id ? $model->getName() : __('New Profile'));
        $resultPage->getLayout()
            ->getBlock('profile_edit_js')
            ->setIsPopup((bool)$this->getRequest()->getParam('popup'));
        
        return $resultPage;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function createActionPage()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        if ($this->getRequest()->getParam('popup')) {
            $resultPage->addHandle(['popup', 'partfinder_partfinder_profile_edit_popup']);
            $pageConfig = $resultPage->getConfig();
            $pageConfig->addBodyClass('attribute-popup');
        }
        $resultPage->getConfig()->getTitle()->prepend(__('New Profile'));
        return $resultPage;
    }
}
