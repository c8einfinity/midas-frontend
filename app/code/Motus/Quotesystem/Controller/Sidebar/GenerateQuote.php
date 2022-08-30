<?php
/**
 * Update miniquote.
 */
namespace Motus\Quotesystem\Controller\Sidebar;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Psr\Log\LoggerInterface;

class GenerateQuote extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * construct
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Motus\Quotesystem\Model\QuoteDetailsFactory $motQuotes
     * @param \Motus\Quotesystem\Helper\Data $helper
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        \Motus\Quotesystem\Model\QuoteDetailsFactory $motQuotes,
        \Motus\Quotesystem\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->logger = $logger;
        $this->motQuotes = $motQuotes;
        $this->_helper = $helper;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $customerId = $this->_customerSession->getCustomerId();
        try {
            $quoteDetailsColl = $this->motQuotes->create()->getCollection()
                                                ->addFieldToFilter('customer_id', $customerId)
                                                ->addFieldToFilter('is_active', 1);
            
            if ($quoteDetailsColl->getSize()) {
                $quoteId = $quoteDetailsColl->getFirstItem()->getId();
                $quoteModel = $this->motQuotes->create()->load($quoteId);
                $quoteModel->setQuoteGenerate(1)
                            ->setIsActive(0);
                $quoteModel->save();
                $this->messageManager->addSuccess(__('Quote has successfully Saved.'));
            } else {
                $this->messageManager->addError(__('There is no product in the quote.'));
            }
            
        } catch (LocalizedException $e) {
            $this->messageManager->addError(__('Quote is not Saved.'));
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Quote is not Saved.'));
            $this->logger->critical($e);
        }
        return $this->resultRedirectFactory
                    ->create()->setPath(
                        'quotesystem/buyerquote/index',
                        ['_secure'=>$this->getRequest()->isSecure()]
                    );
    }
}
