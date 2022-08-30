<?php
/**
 * Sales Order Place after Observer
 */

namespace Motus\Quotesystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Motus\Quotesystem\Helper\Data;
use \Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\Quote\ItemFactory;
use Motus\Quotesystem\Model\ResourceModel\Quotes\CollectionFactory;

class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var \Motus\Quotesystem\Helper\Data
     */
    protected $_helper;
    /**
     * @var Magento\Quote\Model\QuoteFactory
     */
    protected $_salesquote;
    /**
     * @var Magento\Quote\Model\Quote\ItemFactory
     */
    protected $_salesquoteItem;
    /**
     * @var Motus\Quotesystem\Model\ResourceModel\Quotes\CollectionFactory
     */
    protected $_quoteCollectionFactory;

    /**
     * @param Data         $helper
     * @param QuoteFactory $salesQuote
     * @param ItemFactory  $salesquoteItem
     */

    public function __construct(
        Data $helper,
        QuoteFactory $salesQuote,
        ItemFactory $salesquoteItem,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepositoryinterface,
        CollectionFactory $quoteCollectionFactory,
        \Magento\Framework\Session\SessionManager $coreSession
    ) {
        $this->_helper = $helper;
        $this->_salesquote = $salesQuote;
        $this->_salesquoteItem = $salesquoteItem;
        $this->_quoteCollectionFactory = $quoteCollectionFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->orderRepositoryinterface = $orderRepositoryinterface;
        $this->coreSession = $coreSession;
    }
    /**
     * Sales order place after event
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isMultiShipping = $this->_checkoutSession->getQuote()->getIsMultiShipping();
        if (!$isMultiShipping) {
            $order = $observer->getOrder();
            $incrementId = $order->getIncrementId();
            $quoteId = 0;
            $store = $this->_helper->getStore();
            $salesQuoteCollection = $this->_salesquote->create()->setStore($store)
                ->getCollection()
                ->addFieldToFilter('reserved_order_id', $incrementId);

            if (count($salesQuoteCollection)) {
                foreach ($salesQuoteCollection as $salesQuote) {
                    $quoteId = $salesQuote->getEntityId();
                }
            }
            if ($quoteId != 0) {
                $quoteModel = $this->_salesquote->create()->load($quoteId);
                $quoteItemModel = $this->_salesquoteItem->create()
                    ->setStore($store)
                    ->getCollection()
                    ->setQuote($quoteModel);
                $this->setTableRecords($quoteItemModel);
            }
        } else {
            $quoteId = $this->_checkoutSession->getLastQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            if ($quote->getIsMultiShipping() == 1 || $isMultiShipping == 1) {
                $orderIds = $this->coreSession->getOrderIds();
                foreach ($orderIds as $ids => $orderIncId) {
                    $quoteId = 0;
                    $store = $this->_helper->getStore();
                    $salesQuoteCollection = $this->_salesquote->create()->setStore($store)
                        ->getCollection()
                        ->addFieldToFilter('reserved_order_id', $orderIncId);
                    $quoteId = $this->getQuoteId($salesQuoteCollection);
                    if ($quoteId != 0) {
                        $quoteModel = $this->_salesquote->create()->load($quoteId);
                        $quoteItemModel = $this->_salesquoteItem->create()
                            ->setStore($store)
                            ->getCollection()
                            ->setQuote($quoteModel);
                        $this->setTableRecords($quoteItemModel);
                    }
                }
            }
        }
    }

    public function getQuoteId($salesQuoteCollection)
    {
        $quoteId = 0;
        if (count($salesQuoteCollection)) {
            foreach ($salesQuoteCollection as $salesQuote) {
                $quoteId = $salesQuote->getEntityId();
            }
        }
        return $quoteId;
    }

    public function setTableRecords($quoteItemModel)
    {
        foreach ($quoteItemModel as $quoteItem) {
            $updatedQuoteIds = [];
            $quoteCollection = $this->_quoteCollectionFactory
                ->create()
                ->addFieldToFilter('item_id', $quoteItem->getItemId());
            if (count($quoteCollection)) {
                foreach ($quoteCollection as $quote) {
                    if ($quote->getEntityId() != 0) {
                        $updatedQuoteIds[] = $quote->getEntityId();
                    }
                }
            }
            if (count($updatedQuoteIds)) {
                $coditionArr = [];
                foreach ($updatedQuoteIds as $key => $id) {
                    $condition = "`entity_id`=".$id;
                    array_push($coditionArr, $condition);
                }
                $coditionData = implode(' OR ', $coditionArr);

                $quotesCollection = $this->_quoteCollectionFactory
                    ->create();
                $quotesCollection->setTableRecords(
                    $coditionData,
                    ['status' => 4]
                );
            }
        }
    }
}
