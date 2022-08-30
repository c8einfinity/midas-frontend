<?php
/**
 * Motus
 */

namespace Motus\Quotesystem\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class QuoteSection extends \Magento\Framework\DataObject implements SectionSourceInterface
{

    public function __construct(
        \Motus\Quotesystem\Model\QuoteDetailsFactory $quoteDetails,
        \Motus\Quotesystem\Model\QuotesFactory $quote,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Motus\Quotesystem\Helper\Data $quoteHelper,
        array $data = []
    ) {
        $this->quoteDetails = $quoteDetails;
        $this->quote = $quote;
        $this->_customerSession = $customerSession;
        $this->productTypeInstance = $productTypeInstance;
        $this->product = $product;
        $this->imageHelper = $imageHelper;
        $this->checkoutHelper = $checkoutHelper;
        $this->quoteHelper = $quoteHelper;
        parent::__construct($data);
    }
    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $miniquoteData = $this->getMiniquoteData();
        return [
            'summary_count' => $miniquoteData['count'],
            'items' => $miniquoteData['item'],
        ];
    }

    public function getMiniquoteData()
    {
        $returnData['count'] = 0;
        $returnData['item'] = [];
        $customerId = $this->_customerSession->getCustomerId();
        $quoteDetailsColl = $this->quoteDetails->create()->getCollection()
                                                    ->addFieldToFilter('customer_id', $customerId)
                                                    ->addFieldToFilter('is_active', 1);
        if ($quoteDetailsColl->getSize()) {
            $quoteId = $quoteDetailsColl->getFirstItem()->getId();
            $quote = $this->quote->create()->getCollection()
                                        ->addFieldToFilter('quote_id', $quoteId);
            $returnData['count'] = $quote->getSize();
            if ($quote->getSize()) {
                $i = 0;
                foreach ($quote as $data) {
                    $productObj = $this->product->create();
                    $product = $this->product->create()->load($data->getProductId());
                    $imageHelper = $this->imageHelper->init($product, 'mini_cart_product_thumbnail');
                    $productUrl = $product->getUrlModel()->getUrl($product);
                    $options = $this->getProductAttributeOptions($product, $data);
                    if ($productUrl) {
                        $productHasUrl = true;
                    } else {
                        $productHasUrl = false;
                    }
                    $return[$i] = [
                        'product_name' => $data->getProductName(),
                        'qty' => $data->getQuoteQty(),
                        'quote_price' => $this->checkoutHelper->formatPrice($data->getQuotePrice()),
                        'product_has_url' => $productHasUrl,
                        'product_url' => $productUrl,
                        'product_image' => [
                            'src' => $imageHelper->getUrl(),
                            'alt' => $imageHelper->getLabel(),
                            'width' => $imageHelper->getWidth(),
                            'height' => $imageHelper->getHeight(),
                        ],
                        'options' => $options,
                        'bundle_options' => $this->getBundleOptions($product, $data),
                        'canApplyMsrp' => false,
                        'item_id' => $data->getEntityId(),
                        'product_sku' => $product->getSku(),
                    ];
                    $i++;
                }
                $returnData['item'] = $return;
            }
        }

        return $returnData;
    }

    /**
     * Get configurable product options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Motus\Quotesystem\Model\Quotes $data
     * @return void
     */
    public function getProductAttributeOptions($product, $data)
    {
        $optionsArray = [];
        $productAttributeOptions = $this->productTypeInstance->getConfigurableAttributesAsArray($product);
        if ($productAttributeOptions) {
            $superAttribute = json_decode($data->getSuperAttribute());
            $attribute = [];
            foreach ($superAttribute as $key => $value) {
                $attribute[$key] = $value;
            }
            $count = 0;
            foreach ($productAttributeOptions as $id => $options) {
                foreach ($options['values'] as $value) {
                    if (isset($attribute[$id])) {
                        if ($value['value_index'] == $attribute[$id]) {
                            $optionsArray[$count] = [
                                'label' => $options['label'],
                                'value' => $value['label'],
                                'option_id' => $id,
                                'option_value' => $value['value_index'],
                            ];
                            $count++;
                        }
                    }
                }
            }
        }
        
        return $optionsArray;
    }

    /**
     * Get bundle product options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Motus\Quotesystem\Model\Quotes $data
     * @return void
     */
    public function getBundleOptions($product, $quote)
    {
        $bundleOptionsArr = [];
        if ($product->getTypeId() == 'bundle') {
            $optionAndPrice = $this->quoteHelper->getOptionNPrice($product, $quote);
            $optionAndPriceArr = explode("~|~", $optionAndPrice);
            $options = explode("</dd>", $optionAndPriceArr[0]);
            foreach ($options as $option) {
                $bundleOptionsArr[] = strip_tags($option);
            }
        }
        return $bundleOptionsArr;
    }
}
