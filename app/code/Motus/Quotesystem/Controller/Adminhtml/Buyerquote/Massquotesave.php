<?php
/**
 * Save quote at admin end.
 */

namespace Motus\Quotesystem\Controller\Adminhtml\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Url;

class Massquotesave extends Action
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalogProduct;

    /**
     * construct
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $catalogProduct
     * @param \Magento\Customer\Model\Url $url
     * @param \Motus\Quotesystem\Controller\Adminhtml\Buyerquote\Saveemailquote $saveQuote
     */
    public function __construct(
        Context $context,
        ProductFactory $catalogProduct,
        Url $url,
        \Motus\Quotesystem\Controller\Adminhtml\Buyerquote\Saveemailquote $saveQuote
    ) {
        $this->_catalogProduct = $catalogProduct;
        $this->saveQuote = $saveQuote;
        $this->_url = $url;
        parent::__construct($context);
    }

    /**
     * Save quote from buyer.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $result = [];
        $resultMsg = 0;
        if (!$this->getRequest()->isPost()) {
            $redirectUrl = $this->_url->getUrl('quotesystem/managequotes/index/');
            $this->messageManager->addError(
                __(
                    "Sorry some error occured!!!"
                )
            );
            return;
        }
        $params = $this->getRequest()->getParams();
        if (!is_array($params)) {
            $this->messageManager->addError(
                __("Sorry!! Quote can't be saved.")
            );
            return;
        }
        $productIds = explode(",", $params['product_ids']);
        $i = 0;
        foreach ($productIds as $productId) {
            $newArr = [];
            $params ["product"] = $productId;
            $product = $this->_catalogProduct->create()->load($productId);
            if ($product->getTypeId() == 'configurable') {
                $attributeData = json_decode($params[$productId]);
                
                foreach ($attributeData as $key => $value) {
                    $newArr[$key] = $value;
                }
                $params["super_attribute"] = $newArr;

            } else {
                $params["super_attribute"] = "";
            }
            if (isset($params["customOption".$productId]) && !empty($params["customOption".$productId])) {
                $customOptionData = json_decode($params["customOption".$productId]);
                $arrTemp = [];
                foreach ($customOptionData as $key => $data) {
                    $key = $key[8];
                    $arrTemp[$key] = $data;
                }
                $params["options"] = $arrTemp;
            }
            $errors = $this->validateData($params);
            if (empty($errors)) {
                $result = $this->saveQuote->saveQuoteData($params, $i);
                if ($result) {
                    $resultMsg ++;
                }
                $i++;
            } else {
                foreach ($errors as $message) {
                    $this->messageManager->addError($message);
                }
                return;
            }
        }
        if ($resultMsg == 0) {
            $this->messageManager
                ->addSuccess(__("Your Quote has been successfully emailed"));
        }
    }

    /**
     * validates quote's data added by customer.
     *
     * @return bool
     */
    public function validateData(&$params)
    {
        $errors = [];
        $data = [];
        foreach ($params as $code => $value) {
            switch ($code) {
                case 'quote_qty':
                    $validator = new \Zend_Validate_Int();
                    if (!$validator->isValid($value)) {
                        $errors[] = __('Quote Quantity can contain only integer value');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $params[$code] = $value;
                    }
                    break;
                case 'quote_price':
                    $validator = new \Zend_Validate_Float();
                    if (!$validator->isValid($value)) {
                        $errors[] = __('Quote Price can contain only decimal or integer value');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $params[$code] = $value;
                    }
                    break;
                case 'quote_description':
                    if (trim($value) == '') {
                        $errors[] = __('Please enter the quote description');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $params[$code] = $value;
                    }
                    break;
            }
        }

        return $errors;
    }
}
