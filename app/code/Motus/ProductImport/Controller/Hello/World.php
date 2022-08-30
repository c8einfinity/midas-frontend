<?php
/**
 * MageHelper Print Hello World Simple module
 *
 * @package      MageHelper_PrintHelloWorld
 * @author       Kishan Savaliya <kishansavaliyakb@gmail.com>
 */

namespace Motus\ProductImport\Controller\Hello;

use Magento\Setup\Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Motus\ProductImport\Service;

class World extends \Magento\Framework\App\Action\Action
{


    protected $_URI_LIVE = 'http://motusproductstage.azurewebsites.net/api/product';
    protected $_URI_TEST = 'http://motusproductstage.azurewebsites.net/api/product';

    protected $_IMAGE_URI = 'https://motusimagehost.azurewebsites.net/products/';

    protected $_DEFAULT_CATEGORY_ID = 2;

    //This product group breaks -- need to investigate
    //protected $_SKIP_ROWS = 800;
    //protected $_GET_ROWS = 100;

    protected $_SKIP_ROWS = 1900;
    protected $_GET_ROWS = 100;


    /**
     * @var  \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $_objectManager;

    protected $_productRepository;

    protected $file;
    protected $directoryList;
    protected $_dir;

    /**
     * @var  \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $_httpClientFactory;

    protected $_collectionFactory;

    protected $_imageImportService;


    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collecionFactory,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
    ) {
        $this->_dir = $dir;
        $this->_httpClientFactory = $httpClientFactory;
        $this->_productRepository = $productRepository;
        $this->_collectionFactory = $collecionFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $myDate = date('m/d/Y');
        echo "<h1>CPD Product Import on {$myDate}.</h1>";

        $prods = $this->getCpdProducts();

        echo '<hr>';

        $prodCount = 0;

        foreach ($prods as $pr){
            //echo "Sku : " . $pr["sku"] . "<br/>";
            $prodCount++;
            $this->LoadProduct($pr);
        }
        echo "{$prodCount} products Loaded";

        echo '<br/> =================== <br />';
        echo '<br/> ======= E N D ===== <br />';
    }

    public function getCpdProducts()
    {
        $JSON = (object) [
            "SkipNoOfRows" => $this->_SKIP_ROWS,
            "GetNoOfRows" => $this->_GET_ROWS
        ];

        $jstr = json_encode($JSON);
        echo '<hr>' . $jstr . '<hr>';

        $client = $this->_httpClientFactory->create();
        $client->setUri('http://motusproductstage.azurewebsites.net/api/productlist');
        //http://motusproductstage.azurewebsites.net/api/product
        //$client->setMethod(\Zend_Http_Client::GET);
        $client->setMethod(\Zend_Http_Client::POST);
        $client->setHeaders('Content-Type', 'application/json');
        $client->setRawData(utf8_encode($jstr));
        $response = $client->request();
        //var_dump($response->getBody());

        $details = json_decode($response->getBody(),true);

        return $details;
    }

    public function LoadProduct($pr)
    {
        echo '<br />';
        echo 'ImportProcess';
        echo '<br />';

        echo 'Product Description : ', $pr['product_desc'] . htmlentities($pr['additional_info']), '<hr>';

        $sku = $pr["sku"];
        $name = $pr["product_name"];
        $price = $pr["retail_price"];
        $prod_desc = $pr["product_desc"] . htmlentities($pr['additional_info']);
        $prod_shortdesc = $pr["short_desc"];
        $metatitle = $pr["product_name"];
        $metaKeyword = $pr["product_name"];
        $metaDesc = $pr["product_desc"];
        $qty = $pr["qty_on_hand"];
        $image_url = $pr["image_url"];

        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        try {
            echo("Searching for Product {$sku}<br/>");
            $product = $this->_productRepository->get($sku, false, null, true);
            echo "{$sku}  Found - UPDATE: " . $product->getId();
            $this->UpdateProductInfo($product, $pr);

        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            echo "{$sku} Not Found - CREATE ";
            //$this->insertProduct($sku, $name, $price, $metatitle, $metaKeyword, $metaDesc, $qty, $prod_desc, $prod_shortdesc, $image_url);
            $this->insertProduct($pr);
        }
    }

    public function insertProduct($pr)
    {

        try {

            $newProduct = $this->_objectManager->create('\Magento\Catalog\Model\Product');
            $webSite_id = [1];

            $newProduct->setWebsiteIds($webSite_id);
            $newProduct->setAttributeSetId(4);
            $newProduct->setTypeId('simple');
            $newProduct->setCreatedAt(strtotime('now'));
            $newProduct->setName($pr['product_name']);
            $newProduct->setSku($pr['sku']);
            $newProduct->setWeight(1);
            $newProduct->setStatus(1);
            //$category_id= [2,52,57]; //staging.alert
            //$category_id = [2,3,41];
            //$newProduct->setCategoryIds($category_id);
            $newProduct->setCategoruIds($this->GetProductCategories($pr));

            $newProduct->setTaxClassId(0); // (0 - none, 1 - default, 2 - taxable, 4 - shipping)
            $newProduct->setVisibility(4); // catalog and search visibility
            $newProduct->setColor(24);
            $newProduct->setPrice($pr['retail_price']);
            $newProduct->setCost(1);
            $newProduct->setMetaTitle($pr['product_name']);
            $newProduct->setMetaKeyword($pr['product_name']);
            $newProduct->setMetaDescription($pr['product_desc']);
            $newProduct->setDescription($pr['product_desc'] . htmlentities($pr['additional_info']));
            $newProduct->setShortDescription($pr['short_desc']);

            $newProduct->setStockData(
                [
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1, // manage stock
                    'min_sale_qty' => 1, // Shopping Cart Minimum Qty Allowed
                    'max_sale_qty' => 2, // Shopping Cart Maximum Qty Allowed
                    'is_in_stock' => 1, // Stock Availability of product
                    'qty' => (int)$pr['qty_on_hand']
                ]
            );
            //echo "SAVING PRODUCT";
            $newProduct->save();
            //echo "Inserted Product ID : <b>{$newProduct->getId() }</b>";
        } catch (Exception $ex){
            echo '<b>ERROR INSERTING NEW PRODUCT : ' . $ex->getMessage() . '</b>';
        }

        if (file_exists($pr['image_url'])){
            $mySaveDir = $this->_dir->getPath('media');
            $filename = basename($pr['image_url']);
            $completeSaveLoc = $mySaveDir. '/' . $filename;
            if(!file_exists($completeSaveLoc)) {
                try {
                    file_put_contents($completeSaveLoc, file_get_contents($pr['image_url']));
                    //move uploaded file
                } catch (Exception $e) {
                    echo '<BR> ERRROR:::' . $e->getMessage();
                }
            }

            try {
                $newProduct->addImageToMediaGallery($completeSaveLoc, array('image','thumbnail','small_image'), true, false);
                $newProduct->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage());
                echo "ERROR Saving IMAGES to Product Message : {$e->getMessage()} ";
            }

        } else {
            //No Image is available for this product
            echo "<h3><b><i>Image for sku {$pr['sku']} does not exist on source {$pr['image_url']}</i></b></h3>";
        }

        $this->DisplayProductInfo($newProduct->getId(), "<b>INSERT</b>", $pr['sku'], $pr['product_desc'], $pr['retail_price']);

    }

    public function DisplayProductInfo($id, $action, $sku, $desc, $price){
        echo $action . " ID: " . $id ." product sku : " . $sku . " desc : " . $desc . " price : " . $price . "<br>";
    }


    public function UpdateProductInfo($product, $pr){

        //Determine the Category IDs for this product
        $categories = $this->GetProductCategories($pr);

        //var_dump($categories);

        //Set the product info
        $product->setWebsiteIds([1]);
        $product->setAttributeSetId(4);
        $product->setTypeId('simple');
        $product->setCreatedAt(strtotime('now'));
        $product->setName($pr['product_name']);
        $product->setSku($pr['sku']);
        $product->setWeight(1);
        $product->setStatus(1);
        //$category_id= [2,52,57]; //Staging.Alert
        //$category_id = [2,3,41]; //alert.localdev.com

        //$product->setCategoryIds($category_id);
        $product->setCategoryIds($categories);

        $prodDesc = "<p>{$pr['product_desc']}</p>{$pr['additional_info']}";

        $product->setTaxClassId(0); // (0 - none, 1 - default, 2 - taxable, 4 - shipping)
        $product->setVisibility(4); // catalog and search visibility
        $product->setColor(24);
        $product->setPrice($pr['retail_price']);
        $product->setCost(1);
        $product->setMetaTitle($pr['product_name']);
        $product->setMetaKeyword($pr['product_name']);
        $product->setMetaDescription($pr['product_desc']);
        $product->setDescription($prodDesc);
        //$product->setDescription($pr['product_desc'] . htmlentities($pr['additional_info']));
        $product->setShortDescription($pr['short_desc']);

        $product->setStockData(
            [
                'use_config_manage_stock' => 0,
                'manage_stock' => 1, // manage stock
                'min_sale_qty' => 1, // Shopping Cart Minimum Qty Allowed
                'max_sale_qty' => 999, // Shopping Cart Maximum Qty Allowed
                'is_in_stock' => 1, // Stock Availability of product
                'qty' => (int)$pr['qty_on_hand']
            ]
        );

        $product->save();



        //Check if the image exists on the url provided
        //echo '<hr><a href="'. $pr['image_url'] . '"> ' . '<img src="'. $pr['image_url'] . '" alt="'. $pr['image_url'] . '">' . '</a>';
        if (file_exists(strtoupper($pr['image_url']))) {
            //echo '<a href="'. $pr['image_url'] . '"> ' . '<img src="'. $pr['image_url'] . '" alt="'. $pr['image_url'] . '">' . '>';

            //Clear exisitng media galliers
            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();

            foreach ($existingMediaGalleryEntries as $key => $entry) {
                //We can add your condition here
                unset($existingMediaGalleryEntries[$key]);
                echo('Removing Medial Gallery' . $key . '<br>');
            }

            $mySaveDir = $this->_dir->getPath('media');
            //echo "<hr>SaveDir : " .  $mySaveDir;
            //echo '<img src="'. $pr['image_url'] .'" alt="' . $pr['sku'] . '">';

            $filename = strtoupper(basename($pr['image_url']));
            //echo '<br> filename: ' . $filename;

            $completeSaveLoc = $mySaveDir . '/' . $filename;
            if (!file_exists($completeSaveLoc)) {
                try {
                    file_put_contents($completeSaveLoc, file_get_contents($pr['image_url']));
                    //move uploaded file
                } catch (Exception $e) {
                    echo '<BR> ERRROR:::' . $e->getMessage();
                }
            } else {
                //echo "<br>FILE EXIST " . $completeSaveLoc . "<br/>";
            }
            try {
                $product->addImageToMediaGallery($completeSaveLoc, array('image', 'thumbnail', 'small_image'), true, false);
                $product->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage());
            }
        } else {
            $completeSaveLoc = $this->_dir->getPath('media') . '/' . strtoupper(basename($pr['image_url']));
            if (file_exists($completeSaveLoc)){

                $product->addImageToMediaGallery($completeSaveLoc, array('image', 'thumbnail', 'small_image'), true, false);
                $product->save();
                echo "Local Image Added to Gallery {$completeSaveLoc} \n";
            }
            else {
                echo "Local Image Does not Exist {$completeSaveLoc} \n";
            }
            //No Image is available for this product
            echo "<h3><b><i>Image for sku {$pr['sku']} does not exist on source {$pr['image_url']}</i></b></h3>";
        }

        $this->DisplayProductInfo($product->getId(), "UPDATE", $pr['sku'], $pr['product_desc'], $pr['retail_price']);

    }


    public function GetProductCategories($pr){
        // Find Default Category Name

        $category_list = array();

        $category_list[] = 2; //Default Category Top Level
        $category_name = 'BRANDS';
        $parent_cat_id = 2;
        //echo "Categopry {$category_name}";
        if($category_name!="") {
            $brandParentId = $this->save_category($category_name, $parent_cat_id);
        }
        //echo "Categopry ID {$brandParentId} for Category {$category_name}<br>";

        $brandName = $pr['supplier_name'];
        if($pr['supplier_name']!="") {
            $brandCategoryId = $this->save_category($brandName, $brandParentId);
            $category_list[] = $brandCategoryId;
        }
        //echo "Categopry ID {$brandCategoryId} for Category {$brandName}<br>";

        $idx = 0;
        $productTopCatId = $brandCategoryId;
        $cats = explode(',',$pr['category_list']);
        //echo "exploding cats <br>";
        //var_dump($cats);

        echo "First Category {$cats[0]}<br>";
        foreach ($cats as $c){
            if ($c != ""){
                $catid = $this->save_category($c, $productTopCatId);
                $category_list[] = $catid;
                //echo "Categopry ID {$catid} for Category {$c}<br>";
            }
            if ($idx == 0){
                $productTopCatId = $catid; //Set the top level category for all subsequest categories
                $idx++;
            }
        }

        return $category_list;

    }
    //
    // This function will return the category id for the supplied name, for the supplied parent.
    // the category will be created if it does not exist
    //
    public function save_category($CategoryName, $parentId) {
        echo "<br>SAVE CATEGORY FUCTION : Category Name {$CategoryName} ... Parent {$parentId}";

        if($CategoryName!=NULL) {
            //$parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID; //This will return value 1
            //$parentId = 2; // We have set parent category as a DEFAULT CATEGORY
            //$parentId = $ParentCatId;
            $parentCategory = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($parentId);
            $category = $this->_objectManager->create('Magento\Catalog\Model\Category');

            //Check exist category
            $cate = $category->getCollection()
                ->addAttributeToFilter('name',$CategoryName)
                ->getFirstItem();

            if($cate->getId()==NULL) {
                $category->setPath($parentCategory->getPath())
                    ->setParentId($parentId)
                    ->setName($CategoryName)
                    ->setIsActive(true);
                $category->save();
                return $category->getId();
            } else {
                return $cate->getId();
            }
        } else {
            return "Please enter valid category name.";
        }
    }


}
