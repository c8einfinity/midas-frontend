<?php
/**
 Do you wish to enable Quote on this product
 */

namespace Motus\Quotesystem\Model\Plugin\Quote;

use Magento\Framework\Registry;

class Item
{
    protected $quoteHelper;

    protected $request;

    /**
     * @param \Motus\Quotesystem\Helper\Data     $quoteHelper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Motus\Quotesystem\Helper\Data $quoteHelper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->quoteHelper = $quoteHelper;
        $this->request = $request;
    }
    public function afterRepresentProduct(
        \Magento\Quote\Model\Quote\Item $subject,
        $result
    ) {
        if ($result==true) {
            $helper = $this->quoteHelper;
            if ($quoteId = $helper->isQuoteItem($subject)) {
                if ($quoteId!=0) {
                    return false;
                }
            }
            $params = $this->request->getParams();
            $quoteItems = $helper->getCheckoutSession()->getQuote()->getAllItems();
            if (count($quoteItems)) {
                foreach ($quoteItems as $item) {
                    if ($item->getParentItem()) {
                        continue;
                    }
                    if (array_key_exists('product', $params) && $item->getProductId()==$params['product']) {
                        return (bool)(!($helper->checkQuoteProductinItem($item)));
                    }
                }
            }
        }
        return $result;
    }
}
