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
namespace Webkul\PartFinder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Escaper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Data helper
 */
class Data extends AbstractHelper
{
     /**
     * File Error Constant
     */
    const FILE_ERROR = 1;

    /**
     * Profiler Error Constant
     */
    const PROFILER_ERROR = 2;

    /**
     * @var array
     */
    protected $fileData = [];

    /**
     * @var int
     */
    protected $rowCount = 0;

    /**
     * @var array
     */
    protected $fileColumns = [];

    /**
     * @var array
     */
    protected $dropdownData = [];

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepositry;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;
    /**
     *
     *
     */
    protected $dropdownOption;

    /**
     * @param Context $context
     * @param Filesystem $filesystem
     * @param UrlInterface $urlBuilder
     * @param CurrencyInterface $localeCurrency
     * @param SessionManagerInterface $session
     * @param StoreManagerInterface $storeManager
     * @param ProductRepository $productRepositry
     * @param ProductHelper $productHelper
     * @param ImageHelper $imageHelper
     * @param StockRegistryInterface $stockRegistry
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        UrlInterface $urlBuilder,
        CurrencyInterface $localeCurrency,
        SessionManagerInterface $session,
        StoreManagerInterface $storeManager,
        ProductRepository $productRepositry,
        ProductHelper $productHelper,
        ImageHelper $imageHelper,
        StockRegistryInterface $stockRegistry,
        \Webkul\PartFinder\Api\Data\DropdownOptionInterfaceFactory $dropdownOption,
        \Magento\Framework\App\RequestInterface $request,
        Escaper $escaper
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->urlBuilder = $urlBuilder;
        $this->localeCurrency = $localeCurrency;
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->productRepositry = $productRepositry;
        $this->productHelper = $productHelper;
        $this->imageHelper = $imageHelper;
        $this->dropdownOption = $dropdownOption;
        $this->request = $request;
        $this->stockRegistry = $stockRegistry;
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
    }

    /**
     * return media path
     *
     * @return string
     */
    public function getCsvUrl()
    {
        $mediaDirectory = $this->storeManager
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $url = $mediaDirectory.'partfinder/samples/sample.csv';
        return $url;
    }

    /**
     * conver csv data into array
     *
     * @param array $fileData
     * @return array
     */
    public function processCsv($fileData)
    {
        $absolutePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath().'partfinder'.$fileData['file'];
              
        $csv = array_map('str_getcsv', file($absolutePath));
       
        $headers = $csv[0];
        foreach ($headers as $key => $value) {
            $updated = preg_replace(
                '/
                  ^
                  [\pZ\p{Cc}\x{feff}]+
                  |
                  [\pZ\p{Cc}\x{feff}]+$
                 /ux',
                '',
                $value
            );
            $headers[$key] = $updated;
        }
        $this->fileColumns = $headers;
        unset($csv[0]);
        $rowsWithKeys = [];
        foreach ($csv as $row) {
            $newRow = [];
            foreach ($headers as $k => $key) {
                $newRow[$key] = $row[$k];
            }
            $this->fileData[] = $newRow;
        }
        
        $this->rowCount = count($this->fileData);
    }

    /**
     * Total row to import
     *
     * @return int|string
     */
    public function getTotalRows()
    {
        return (int) $this->rowCount;
    }

    /**
     * Validate mapping column and csv column
     *
     * @param array $dropdowns
     * @return bool|string
     */
    public function validate($dropdowns)
    {
        $fileData = $this->fileData;
        $notFoundColumns = [];
        $extraColumns = [];
        $data = [];
        if (!empty($dropdowns)) {
            $notFoundColumns = array_diff($dropdowns, $this->fileColumns);
            $extraColumns = array_diff($this->fileColumns, $dropdowns);
        }
        if (!empty($notFoundColumns)) {
            $data['error'] = self::FILE_ERROR;
            $data['columns'] = implode(',', $notFoundColumns);
            return $data;
        }
        if (!empty($extraColumns)) {
            $data['error'] = self::PROFILER_ERROR;
            $data['columns'] = implode(',', $extraColumns);
            return $data;
        }
        
        return true;
    }

    /**
     * set data to session
     *
     * @return boolean
     */
    public function saveProfileData()
    {
        $this->session->setFileData($this->fileData);
        $this->session->setTotalRow($this->rowCount);
        $this->session->setDropdownData($this->dropdownData);
        return true;
    }

    /**
     * @param string $dropdowns
     * @return void
     */
    public function processDropdowns($dropdowns)
    {
        $dropdowns =  json_decode($dropdowns, true);
        $this->dropdownData = $dropdowns;
    }

    /**
     * validation if the same name option exists
     *
     * @param string $optionLabel
     * @param array $previousDropdowns
     * @return string|bool
     */
    protected function checkOptionAlreadyExists($optionLabel, $previousDropdowns)
    {
        $optionId = false;
        if (!empty($previousDropdowns) && $previousDropdowns['dropdowns']) {
            foreach ($previousDropdowns['dropdowns'] as $dropdowns) {
                foreach ($dropdowns as $dropdown) {
                    foreach ($dropdown['options'] as $option) {
                        if (trim($option['label']) === trim($optionLabel)) {
                            $optionId =  $option['id'];
                        }
                    }
                }
            }
        }
        
        return $optionId;
    }

    /**
     * Create dropdowns for variations
     *
     * @param string $dropdowns
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function generateDropdowns($dropdowns, $fileData, $previousDropdowns)
    {
        $dropdaownData = $this->getAllDropdowns();
        $uniqLabelKeyOption = [];
        foreach ($dropdaownData as $key => $value) {
            # code...
            $uniqLabelKeyOption[$value["label"]]=$value;
        }
        $level = 'ERROR';
        $previousDropdowns = json_decode($previousDropdowns, true);
        
        foreach ($dropdowns as $value) {
            if ($value['column_name'] == 'sku') {
                continue;
            }
            $this->dropdownData[$value['column_name']] = [
                'dropdown_label' => $value['title'],
                'dropdown_id' => $value['record_id']+1,
                'options' => [],
                'newDropdown' => true,
                'chosenOptions' => [],
                'index' => $value['record_id']
            ];
        }
        if (!empty($fileData)) {
            foreach ($fileData as $column => $columnValue) {
                $values = explode(',', $columnValue);
                $options = [];
                $chosenOptions = [];
                if (!empty($values)) {
                    foreach ($values as $optionvalue) {
                        $uniqid = strtoupper(uniqid());
                        $existsOptionId = $this->checkOptionAlreadyExists($optionvalue, $previousDropdowns);
                        if ($existsOptionId) {
                            $uniqid = $existsOptionId;
                        }
                        $entityId=0;
                        if (isset($uniqLabelKeyOption[$optionvalue]) && $column !== 'sku') {
                            $uniqid = $uniqLabelKeyOption[$optionvalue]["value"];
                            $entityId = $uniqLabelKeyOption[$optionvalue]["entity_id"];
                        }
                        if ($entityId > 0) {
                            $isNew = true;
                            //custom code starts
                            $oldData= $this->request->getParam('oldData');
                            foreach ($oldData as $singleData) {
                                if ($column == $singleData["title"]) {
                                    $entityId = $singleData["option_id"];
                                    $isNew = false;
                                }
                            }
                            //custom code ends
                            $options[] = [
                                'entity_id' => $entityId,
                                'attribute_id' => 0,
                                'id' => $uniqid,
                                'is_new' => $isNew,//true,
                                'newDropdown' => true,
                                'label' => $optionvalue
                            ];
                        } else {
                            $options[] = [
                                'attribute_id' => 0,
                                'id' => $uniqid,
                                'is_new' => true,
                                'newDropdown' => true,
                                'label' => $optionvalue
                            ];
                        }
                        $chosenOptions[] = $uniqid;
                    }
                } else {
                    $uniqid = strtoupper(uniqid());
                    $existsOptionId = $this->checkOptionAlreadyExists($columnValue, $previousDropdowns);
                    if ($existsOptionId) {
                        $uniqid = $existsOptionId;
                    }
                    $entityId=0;
                    if (isset($uniqLabelKeyOption[$optionvalue]) && $column !== 'sku') {
                        $uniqid = $uniqLabelKeyOption[$optionvalue]["value"];
                        $entityId = $uniqLabelKeyOption[$optionvalue]["entity_id"];
                    }
                    if ($entityId > 0) {
                        $options[] = [
                            'entity_id' => $entityId,
                            'attribute_id' => 0,
                            'id' => $uniqid,
                            'is_new' => true,
                            'newDropdown' => true,
                            'label' => $optionvalue
                        ];
                    } else {
                        $options[] = [
                            'attribute_id' => 0,
                            'id' => $uniqid,
                            'is_new' => true,
                            'newDropdown' => true,
                            'label' => $optionvalue
                        ];
                    }
                    $chosenOptions[] = $uniqid;
                }
                if ($column !== 'sku') {
                    $this->dropdownData[$column]['options'] = $options;
                    $this->dropdownData[$column]['chosenOptions'] = $chosenOptions;
                }
            }
        }
        return $this->dropdownData;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getProductData($sku)
    {
        $product = $this->productRepositry->get($sku);
        $currency = $this->localeCurrency->getCurrency($product->getStore()->getBaseCurrencyCode());
        $productMatrix[] = [
            'entity_id' => $product->getId(),
            'id' => $product->getId(),
            'product_link' => '<a href="' . $this->urlBuilder->getUrl(
                'catalog/product/edit',
                ['id' => $product->getId()]
            ) . '" target="_blank">' . $this->escaper->escapeHtml($product->getName()) . '</a>',
            'sku' => $this->escaper->escapeHtml($product->getSku()),
            'name' => $this->escaper->escapeHtml($product->getName()),
            'qty' => $this->getProductStockQty($product),
            'price' => $product->getPrice(),
            'price_string' => $currency->toCurrency(sprintf("%f", $product->getPrice())),
            'price_currency' => $product->getStore()->getBaseCurrency()->getCurrencySymbol(),
            'weight' => $product->getWeight(),
            'status' => $product->getStatus(),
            'canEdit' => 0,
            'newProduct' => 0,
            'thumbnail_image' => $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl(),
        ];
        return $productMatrix;
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
     * start final step, generate dropdowns and optiops
     * and products
     *
     * @param int $offset
     * @param int $error
     * @param string $previousDropdowns
     * @return array
     */
    public function startImporting($offset, $error, $previousDropdowns)
    {
        $result = false;
        $message = '';
        $dropdowns = [];
        $products = [];
        try {
            $fileData = $this->session->getFileData();
            $dropdowns = array_values($this->generateDropdowns(
                $this->session->getDropdownData(),
                $fileData[$offset],
                $previousDropdowns
            ));
            $products = $this->getProductData($fileData[$offset]['sku']);
            $result = true;
        } catch (NoSuchEntityException $e) {
            $result = false;
            $message = __('Product with sku "%1" does not exists', $fileData[$offset]['sku']);
            $error = $error++;
        } catch (\Exception $e) {
            $error = $error++;
            $message = __($e->getMessage());
        }
        return [$error, $message, $result, $dropdowns, $products];
    }
    /**
     * @return the dropdown options
     */
    public function getAllDropdowns()
    {
        $data = $this->dropdownOption->create()->getCollection()->getData();
        return $data;
    }
}
