<?php
/**
 * Check product type.
 */

namespace Motus\Quotesystem\Controller\Adminhtml\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\ProductFactory;

class Checkproducttype extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalogProduct;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param Context                                                   $context
     * @param ProductFactory                                            $catalogProduct
     * @param \Magento\Framework\Json\Helper\Data                       $jsonHelper
     */
    public function __construct(
        Context $context,
        ProductFactory $catalogProduct,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_catalogProduct = $catalogProduct;
        $this->_jsonHelper = $jsonHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $productId = $params['id'];
        try {
            $product = $this->_catalogProduct->create()->load($productId);

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultJson = $this->resultJsonFactory->create();
        $result = [
            "type" => $product->getTypeId(),
            "name" => $product->getName(),
            "hasCustomOption" => count($product->getOptions())
        ];
        return $resultJson->setData($result);
    }
}
