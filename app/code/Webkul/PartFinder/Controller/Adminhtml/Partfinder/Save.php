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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Webkul\PartFinder\Model\PartfinderFactory;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var Initialization\Helper
     */
    protected $initializationHelper;
    
    /**
     * @var \Webkul\PartFinder\Model\PartfinderFactory
     */
    protected $partfinderFactory;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param Initialization\Helper $initializationHelper
     * @param PartfinderFactory $partfinderFactory
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        Initialization\Helper $initializationHelper,
        PartfinderFactory $partfinderFactory
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->initializationHelper = $initializationHelper;
        $this->partfinderFactory = $partfinderFactory;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');
            $partfinderModel = $this->partfinderFactory->create();
            if ($id) {
                $partfinderModel->load($id);
            }
            if (!$partfinderModel->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Part finder no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        
            try {
                $partfinder = $this->initializationHelper->initialize(
                    $partfinderModel
                );
                
                $partfinder->save();
                if (isset($data['website_ids'])) {
                    $partfinder->setWebsiteIds($data['website_ids']);
                }
                $partfinder->saveWebsites();
                if (isset($data['category_ids'])) {
                    $partfinder->processCategories($data['category_ids']);
                }
                if (isset($data['finder-matrix']) && $partfinder->getDropdownCount()) {
                    $partfinder->processManualProducts($data['finder-matrix']);
                } else {
                    $partfinder->processManualProducts([]);
                }
                
                $this->messageManager->addSuccessMessage(__('You saved the Partfinder.'));
                $this->dataPersistor->clear('partfinder_data');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $partfinderModel->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the Part finder.')
                );
            }
        
            $this->dataPersistor->set('partfinder_data', $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['entity_id' => $this->getRequest()->getParam('entity_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
