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
use Magento\Ui\Component\MassAction\Filter;
use Webkul\PartFinder\Model\ResourceModel\Partfinder\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

class MassStatus extends Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Webkul\PartFinder\Model\ResourceModel\Partfinder\CollectionFactory
     */
    protected $partfinderCollectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $partfinderCollectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $partfinderCollectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->partfinderCollectionFactory = $partfinderCollectionFactory;
    }

    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->partfinderCollectionFactory->create());
        $status = (int) $this->getRequest()->getParam('status');
        try {
            foreach ($collection as $item) {
                $item->setStatus($status);
                $this->saveObject($item);
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated.', $collection->getSize()));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException(
                $e,
                __('Something went wrong while updating the part finder(s) status.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Save Object
     *
     * @param object $model
     * @return void
     */
    protected function saveObject($model)
    {
        $model->save();
    }
}
