<?php
/**
 Do you wish to enable Quote on this product.
 */

namespace Motus\Quotesystem\Controller\BuyerQuote;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;

class Printpdf extends \Magento\Framework\App\Action\Action
{

    /**
     * construct
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Motus\Quotesystem\Model\QuotePdf $quotePdf
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        \Motus\Quotesystem\Model\QuotePdf $quotePdf,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->quotePdf = $quotePdf;
        $this->logger = $logger;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        try {
            $this->quotePdf->generatePdf($quoteId);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
