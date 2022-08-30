<?php
/**
 Do you wish to enable Quote on this product.
 */
    namespace Motus\Quotesystem\Ui\Component\Listing\Column;

    use Magento\Framework\View\Element\UiComponent\ContextInterface;
    use Magento\Framework\View\Element\UiComponentFactory;
    use Magento\Ui\Component\Listing\Columns\Column;

class TotalPrice extends Column
{
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    protected $helper;

    /**
     * @param ContextInterface                            $context
     * @param UiComponentFactory                          $uiComponentFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Motus\Quotesystem\Helper\Data             $helper
     * @param array                                       $components
     * @param array                                       $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Motus\Quotesystem\Helper\Data $helper,
        \Motus\Quotesystem\Model\QuotesFactory $quotes,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->quotes = $quotes;
    }
    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $store = $this->storeManager->getStore(
                $this->context->getFilterParam('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            );
            $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());

            foreach ($dataSource['data']['items'] as &$item) {

                $item['total_price'] = $currency->toCurrency(sprintf("%f", $this->getTotalPrice($item['quote_id'])));
            }
        }
        return $dataSource;
    }

    /**
     * get Total Price of quote
     *
     * @param int $quoteId
     * @return int
     */
    public function getTotalPrice($quoteId)
    {
        $totalQuotePrice = 0;
        $quotesCollection = $this->quotes->create()
                                ->getCollection()
                                ->addFieldToFilter('quote_id', $quoteId);
        foreach ($quotesCollection as $quoteData) {
            $totalQuotePrice = $totalQuotePrice + $quoteData->getQuotePrice();
        }
        return $totalQuotePrice;
    }
}
