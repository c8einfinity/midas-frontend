<?php
/**
 * Quote Delete controller Admin panel.
 */

namespace Motus\Quotesystem\Controller\Adminhtml\Managequotes;

use Magento\Backend\App\Action;
use Motus\Quotesystem;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var Motus\Quotesystem\Helper\Data
     */
    protected $_quoteHelper;
    /**
     * @var Motus\Quotesystem\Api\QuoteRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @param Action\Context                           $context
     * @param Quotesystem\Helper\Data                  $quoteHelper
     * @param Quotesystem\Api\QuoteRepositoryInterface $quoteRepository
     */
    public function __construct(
        Action\Context $context,
        Quotesystem\Helper\Data $quoteHelper,
        Quotesystem\Api\QuoteRepositoryInterface $quoteRepository
    ) {
        parent::__construct($context);
        $this->_quoteHelper = $quoteHelper;
        $this->_quoteRepository = $quoteRepository;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Motus_Quotesystem::quotes'
        );
    }
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        /**
 * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
*/
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data && array_key_exists('entity_id', $data)) {
            $quoteId = $data['entity_id'];
            if ($quoteId) {
                try {
                    $this->_quoteRepository->deleteById($quoteId);
                    $this->messageManager->addSuccess(
                        __('Quote is successfully deleted.')
                    );
                    return $resultRedirect->setPath('*/*/');
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException(
                        $e,
                        __('Something went wrong while Deleting the data.')
                    );
                }
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
