<?php
/**
 * Update miniquote.
 */
namespace Motus\Quotesystem\Controller\Sidebar;

use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Psr\Log\LoggerInterface;

class UpdateItem extends Action
{
    /**
     * @var Sidebar
     */
    protected $sidebar;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @param Context $context
     * @param Sidebar $sidebar
     * @param LoggerInterface $logger
     * @param Data $jsonHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        Sidebar $sidebar,
        LoggerInterface $logger,
        Data $jsonHelper,
        \Motus\Quotesystem\Model\QuotesFactory $motQuotes,
        \Motus\Quotesystem\Helper\Data $helper
    ) {
        $this->sidebar = $sidebar;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->motQuotes = $motQuotes;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $itemId = (int)$this->getRequest()->getParam('item_id');
        $itemQty = $this->getRequest()->getParam('item_qty') * 1;

        try {
            $mainProductId = $this->getProdutId($itemId);
            if ($mainProductId != 0) {
                $mainProduct = $this->_helper->getProduct($mainProductId);
                $quoteMinimumQty = $mainProduct->getMinQuoteQty();
            } else {
                $quoteMinimumQty = $product->getMinQuoteQty();
            }
            // get config quote qty
            if (!$quoteMinimumQty) {
                $quoteMinimumQty = $this->_helper->getConfigMinQty();
            }
            if ($quoteMinimumQty <= $itemQty) {
                $this->updateQuoteItem($itemId, $itemQty);
                return $this->jsonResponse();
            } else {
                return $this->jsonResponse(__('Sorry you are not allowed to quote on such a low quantity'));
            }
        } catch (LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Compile JSON response
     *
     * @param string $error
     * @return Http
     */
    protected function jsonResponse($error = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($this->sidebar->getResponseData($error))
        );
    }

    /**
     * Update item in mini quote
     *
     * @param int $itemId
     * @return array
     */
    public function updateQuoteItem($itemId, $itemQty)
    {
        try {
            $quoteModel = $this->motQuotes->create();
            $quoteModel->load($itemId);
            $quoteModel->setQuoteQty($itemQty);
            $quoteModel->save();
        } catch (\Exception $e) {
            return $this->jsonResponse($e->getMessage());
        }
    }

    public function getProdutId($itemId)
    {
        $quoteModel = $this->motQuotes->create();
        $quoteModel->load($itemId);
        return $quoteModel->getProductId();
    }
}
