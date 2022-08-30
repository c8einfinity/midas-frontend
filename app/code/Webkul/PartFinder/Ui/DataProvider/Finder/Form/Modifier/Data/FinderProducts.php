<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_PartFinder
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\PartFinder\Ui\DataProvider\Finder\Form\Modifier\Data;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Webkul\PartFinder\Model\ProductSelection\Type\ManualProductMatrix;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Webkul\PartFinder\Api\DropdownOptionRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FinderProducts
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var array
     */
    protected $manualProductMatrix = [];

    /**
     * @var array
     */
    protected $productAttributes = [];

    /**
     * @var array
     */
    protected $productIds = [];

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var Escaper
     */
    private $escaper;

     /**
      * @param \Webkul\PartFinder\Model\Locator\FinderLocator $locator
      * @param UrlInterface $urlBuilder
      * @param ConfigurableType $configurableType
      * @param ProductRepositoryInterface $productRepository
      * @param StockRegistryInterface $stockRegistry
      * @param VariationMatrix $variationMatrix
      * @param CurrencyInterface $localeCurrency
      * @param JsonHelper $jsonHelper
      * @param ImageHelper $imageHelper
      * @param Escaper $escaper
      * @SuppressWarnings(PHPMD.ExcessiveParameterList)
      */
    public function __construct(
        \Webkul\PartFinder\Model\Locator\FinderLocator $locator,
        UrlInterface $urlBuilder,
        ProductRepositoryInterface $productRepository,
        DropdownOptionRepositoryInterface $optionRepository,
        StockRegistryInterface $stockRegistry,
        ManualProductMatrix $manualMatrix,
        JsonHelper $jsonHelper,
        ImageHelper $imageHelper,
        Escaper $escaper = null
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->productRepository = $productRepository;
        $this->optionRepository = $optionRepository;
        $this->stockRegistry = $stockRegistry;
        $this->manualMatrix = $manualMatrix;
        $this->jsonHelper = $jsonHelper;
        $this->imageHelper = $imageHelper;
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
    }

    /**
     * Get variations manual product matrix
     *
     * @return array
     */
    public function getManualProductMatrix()
    {
        if ($this->manualProductMatrix === []) {
            $this->prepareManualVariations();
        }
        return $this->manualProductMatrix;
    }

    /**
     * Prepare variations
     *
     * @return void
     * @throws \Zend_Currency_Exception
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    protected function prepareManualVariations()
    {

        $variations = $this->getManualVariations();
        $productMatrix = [];
        $dropdowns = [];
        $productIds = [];
        if ($variations) {
            $usedFinderDropdowns = $this->getUsedDropdowns();
            foreach ($variations as $variation) {
                    $product = $this->productRepository->getById($variation['product_id']);
                    $price = $product->getPrice();
                    $variationOptions = [];
                foreach ($usedFinderDropdowns as $dropdown) {
                    if (!isset($dropdowns[$dropdown->getId()])) {
                        $dropdowns[$dropdown->getId()] = [
                            'label' => $dropdown->getLabel(),
                            'id' => $dropdown->getId(),
                            'position' => $dropdown->getSortOrder(),
                            'chosen' => [],
                        ];
                        if (is_array($dropdown->getOptions()) && count($dropdown->getOptions())) {
                            foreach ($dropdown->getOptions() as $option) {
                                $dropdowns[$dropdown->getId()]['options'][$option->getId()] = [
                                    'dropdown_id' => $dropdown->getId(),
                                    'dropdown_label' => $dropdown->getLabel(),
                                    'id' => $option->getId(),
                                    'label' => $option->getLabel(),
                                    'value' => $option->getValue(),
                                ];
                            }
                        }
                    }

                    $variationKeys = explode('-', $variation['variation_key']);
                    foreach ($variationKeys as $key) {
                        $optionId = $key;
                        $variationOption = [];
                        if (isset($dropdowns[$dropdown->getId()]['options'][$key])) {
                            $variationOption = $dropdowns[$dropdown->getId()]['options'][$key];
                            $variationOptions[] = $variationOption;
                        }
                    }
                    $dropdowns[$dropdown->getId()]['chosen'][] = $variationOptions;
                }
                    $productMatrix[] = [
                        'id' => $product->getId(),
                        'product_id' => $product->getId(),
                        'product_link' => '<a href="' . $this->urlBuilder->getUrl(
                            'catalog/product/edit',
                            ['id' => $product->getId()]
                        ) . '" target="_blank">' . $this->escaper->escapeHtml($product->getName()) . '</a>',
                        'sku' => $this->escaper->escapeHtml($product->getSku()),
                        'name' => $this->escaper->escapeHtml($product->getName()),
                        'qty' => $this->getProductStockQty($product),
                        'price' => $price,
                        'configurable_attribute' => $this->getJsonFinderDropdowns($variationOptions),
                        'weight' => $product->getWeight(),
                        'status' => $product->getStatus(),
                        'variationKey' => $this->getVariationKey($variationOptions),
                        'canEdit' => 0,
                        'newProduct' => 0,
                        'attributes' => $this->getTextDropdowns($variationOptions),
                        'thumbnail_image' => $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl(),
                    ];
                    $productIds[] = $product->getId();
            }
        }

        $this->manualProductMatrix = $productMatrix;
        $this->productIds = $productIds;
        $this->productAttributes = array_values($dropdowns);
    }

    /**
     * Get JSON string that contains attribute code and value
     *
     * @param array $options
     * @return string
     */
    protected function getJsonFinderDropdowns(array $options = [])
    {
        $result = [];

        foreach ($options as $option) {
            $result[$option['dropdown_id']] = $option['value'];
        }

        return $this->jsonHelper->jsonEncode($result);
    }

    /**
     * Retrieve qty of product
     *
     * @param Product $product
     * @return float
     */
    protected function getProductStockQty(Product $product)
    {
        return $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId())->getQty();
    }

    /**
     * Prepares text list of used attributes
     *
     * @param array $options
     * @return string
     */
    protected function getTextDropdowns(array $options = [])
    {
        $text = '';
        foreach ($options as $option) {
            if ($text) {
                $text .= ', ';
            }
            $text .= $option['dropdown_label'] . ': ' . $option['label'];
        }

        return $text;
    }

    /**
     * Get variation key
     *
     * @param array $options
     * @return string
     */
    protected function getVariationKey(array $options = [])
    {
        $result = [];

        foreach ($options as $option) {
            $result[] = $option['value'];
        }

        asort($result);

        return implode('-', $result);
    }

    protected function getUsedDropdowns()
    {
        $finder = $this->locator->getFinder();
        return $finder->getDropdownsCollection();
    }

    /**
     * Retrieve all possible attribute values combinations
     *
     * @return array
     */
    protected function getManualVariations()
    {
        return $this->manualMatrix->getManualVariations($this->locator->getFinder());
    }
}
