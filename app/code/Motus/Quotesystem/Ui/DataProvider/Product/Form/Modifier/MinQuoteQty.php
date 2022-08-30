<?php
/**
 Do you wish to enable Quote on this product.
 */
namespace Motus\Quotesystem\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Ui\Component\Form;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Data provider for main panel of product page
 *
 * @api
 *
 */
class MinQuoteQty extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    /**
     * @var LocatorInterface
     *
     */
    protected $locator;

    /**
     * @var ArrayManager
     *
     */
    protected $arrayManager;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager     $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        \Motus\Quotesystem\Helper\Data $helper
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     *
     *
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->customizeMinQtyField($meta);

        return $meta;
    }
    /**
     * {@inheritdoc}
     *
     *
     */
    public function modifyData(array $data)
    {
        return $data;
    }
    /**
     * Customize Weight filed
     *
     * @param  array $meta
     * @return array
     *
     */
    protected function customizeMinQtyField(array $meta)
    {
        $weightPath = $this->arrayManager->findPath('min_quote_qty', $meta, null, 'children');
        if ($weightPath) {
            $meta = $this->arrayManager->merge(
                $weightPath . static::META_CONFIG_PATH,
                $meta,
                [
                    'value' => $this->helper->getConfigMinQty(),
                    'dataScope' => 'min_quote_qty',
                    'validation' => [
                        // 'required-entry' => true,
                        'validate-digits' => true
                    ],
                    'additionalClasses' => 'admin__field-small',
                    'imports' => [
                        'disabled' => '!${$.provider}:' . self::DATA_SCOPE_PRODUCT
                            . '.quote_status:value'
                    ]
                ]
            );
        }

        return $meta;
    }
}
