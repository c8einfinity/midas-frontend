<?php
/**
 * Motus
 */
namespace Motus\Quotesystem\Plugin\Checkout;

use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\Message\ManagerInterface;

class Multishipping
{
    protected $quoteHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    public function __construct(
        \Motus\Quotesystem\Helper\Data $quoteHelper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->quoteHelper = $quoteHelper;
        $this->cart = $cart;
        $this->_messageManager = $messageManager;
    }

    public function aroundSetShippingItemsInformation(
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $subject,
        callable $proceed,
        $info
    ) {
        $helper = $this->quoteHelper;
        $session = $helper->getCheckoutSession();
        foreach ($session->getQuote()->getAllItems() as $item) {
            if ($item->getParentItemId() === null && $item->getItemId() > 0) {
                $allQty = 0;
                foreach ($info as $itemData) {
                    foreach ($itemData as $quoteItemId => $data) {
                        if ($quoteItemId == $item->getItemId()) {
                            $allQty += $data['qty'];
                        }
                        
                    }
                }
                $quoteCollection = $helper->getMotQuoteModel()->getCollection()
                    ->addFieldToFilter("item_id", $item->getItemId());
                if ($quoteCollection->getSize()) {
                    foreach ($quoteCollection as $quote) {
                        $quoteQty = $quote->getQuoteQty();
                    }
                    if ($quoteQty != $allQty) {
                        throw new LocalizedException(__("You can't edit quote items"));
                    }
                }
                
            }
        }
        $result = $proceed($info);
        return $info;
    }

    public function aroundRemoveAddressItem(
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $subject,
        callable $proceed,
        $addressId,
        $itemId
    ) {
        $helper = $this->quoteHelper;
        $address = $subject->getQuote()->getAddressById($addressId);
        $item = $address->getValidItemById($itemId);
        $quoteCollection = $helper->getMotQuoteModel()->getCollection()
        ->addFieldToFilter("item_id", $item->getQuoteItemId());
        if ($quoteCollection->getSize()) {
            $this->_messageManager->addNotice(
                __(
                    "You can't remove quote items"
                )
            );
            return ;
        }
        $result = $proceed($addressId, $itemId);
    }
}
