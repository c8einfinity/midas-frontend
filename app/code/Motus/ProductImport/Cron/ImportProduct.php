<?php

namespace Motus\ProductImport\Cron;

use Magento\Setup\Exception;

class ImportProduct
{

    protected $_URI_LIVE = 'http://motusproductstage.azurewebsites.net/api/product';
    protected $_URI_TEST = 'http://motusproductstage.azurewebsites.net/api/product';
    protected $_IMAGE_URI = 'https://motusimagehost.azurewebsites.net/products/';

    protected $_DEFAULT_CATEGORY_ID = 2;

    protected $_startDate;
    protected $_endDate;
    protected $_noOfProductsAPI = 0;
    protected $_noOfProductsInserted = 0;
    protected $_noOfProductsUpdated = 0;

    protected $writer;
    protected $logger;


    protected $_objectManager;

    protected $_productRepository;

    protected $file;
    protected $directoryList;
    protected $_dir;

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
        //return parent::__construct($context);
    }


    public function execute()
    {
        //Start up the import process
        $this->startUp();

        //This is the main processing
        $this->processImport();

        //End the import process
        $this->closeOff();
        return $this;

    }

    public function startUp(){
        $this->_startDate = date('Y-m-d H:i:s');

        $this->writer = new \Zend\Log\Writer\Stream(BP . '/var/log/prodimp.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($this->writer);
        $this->logger->info("Product Import Started on {$this->_startDate}.");
    }

    public function closeOff(){
        $this->_endDate = date('Y-m-d H:i:s');
        $this->logger->info(" *** Product Import START  on {$this->_startDate}. ***");
        $this->logger->info(" *** Product Import ENDED  on {$this->_endDate}. ***");
        $this->logger->info("****************************************************");
    }

    public function processImport(){

	return;    
	    //echo "CPD Product Import on {date()}.</h1>";
        $this->logger->info(__METHOD__);
        $this->logger->info("CPD Product Import on {date()}.");

        $prods = $this->getCpdProducts();
        $this->logger->info('---------------');
        //$this->logger->info(print_r($prods));
        $this->logger->info('---------------');

        $this->_noOfProductsAPI = count($prods);
        $this->logger->info("Products Received : {$this->_noOfProductsAPI}");

        for ($i = 0; $i <= count($prods); $i++){
            //echo("...{$prods[$i]}...\n");
            echo "------", $i, "------\n";
            $this->ShowPrData($prods[$i]);
            echo "------", $i, "------\n";
            //$this->LoadProduct($prods[$i]);
            //$this->logger->info(print_r($prods[$i]));
        }
/*
        foreach ($prods as $item){
            echo("...{$item['sku']}...\n");
            $this->logger->info(print_r($item));
            $this->LoadProduct($item);
        }
*/
    }

    public function ShowPrData($p)
    {
        $this->logger->info("SKU : {$p["sku"]}");
        echo $p["sku"], "\n";

        try {
            $product = $this->_productRepository->get($p["sku"], false, null, true);
            echo $p["sku"], " - ", $product->getEntityId(), "\n";
            //Set the product info
            $product->setWebsiteIds([1]);
            $product->setAttributeSetId(4);
            $product->setTypeId('simple');
            $product->setCreatedAt(strtotime('now'));
            $product->setName($p["product_name"]);
            $product->setSku($p["sku"]);
            $product->setWeight(1);
            $product->setStatus(1);
            //$category_id= [2,52,57]; //Staging.Alert
            //$category_id = [2,3,41]; //alert.localdev.com

            //$product->setCategoryIds($category_id);
            $product->setCategoryIds([2,49,60]);


            $product->setTaxClassId(0); // (0 - none, 1 - default, 2 - taxable, 4 - shipping)
            $product->setVisibility(4); // catalog and search visibility
            $product->setColor(24);
            if ($p["retail_price"] == 0){
                $p["retail_price"] = 2509.99;
            }
            $product->setPrice($p['retail_price']);
            $product->setCost(1);
            //$product->setMetaTitle($p['product_name']);
            //$product->setMetaKeyword($p['product_name']);
            //$product->setMetaDescription($p['product_desc']);
            $product->setDescription($p['product_desc']);
            $product->setShortDescription($p['short_desc']);

            $product->setStockData(
                [
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1, // manage stock
                    'min_sale_qty' => 1, // Shopping Cart Minimum Qty Allowed
                    'max_sale_qty' => 2, // Shopping Cart Maximum Qty Allowed
                    'is_in_stock' => 1, // Stock Availability of product
                    'qty' => (int)$p["qty_on_hand"]
                ]
            );

            if (file_exists($p["image_url"])) {
                echo "Image URL : ", $p['image_url'], "\n";
            }else {
                echo "NO IMAGE at URL : ", $p['image_url'], "\n";
            }

            $localImageFile = $this->_dir->getPath('media') . '/' . basename($p['image_url']);

            if (!file_exists($localImageFile)){
                echo "No local file {$localImageFile}";
            } else
            {
                echo "... Local File Found: {$localImageFile} ...";
            }

            $product->save();
            echo "Product Saved \n";

        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            //echo "{$sku} Not Found - CREATE ";
            //$this->insertProduct($sku, $name, $price, $metatitle, $metaKeyword, $metaDesc, $qty, $prod_desc, $prod_shortdesc, $image_url);
            //$this->insertProduct($pr);
            echo "\n **** ERROR **** ", $e->getMessage();
        }
    }

    //Get the CPD Products from the API
    public function getCpdProducts()
    {
        $this->logger->info(__METHOD__);
        $client = $this->_httpClientFactory->create();
        $client->setUri('http://motusproductstage.azurewebsites.net/api/product');
        //http://motusproductstage.azurewebsites.net/api/product
        $client->setMethod(\Zend_Http_Client::GET);
        $client->setHeaders('Content-Type', 'application/json');
        $response = $client->request();

        $details = json_decode($response->getBody(),true);
        //$details = json_decode($response->getBody());

        return $details;
    }


    public function LoadProduct($pr)
    {
        $this->logger->info(__METHOD__);

        $this->logger->info("Processing ....... Product {$pr["sku"]}");
        $this->logger->info("           ....... Name    {$pr["product_name"]}");
        $this->logger->info("           ....... Desc    {$pr["product_desc"]}");
        $this->logger->info("           ....... Image {$pr["image_url"]}");
        $this->logger->info("           ....... Price {$pr["retail_price"]}");


        $sku = $pr["sku"];
        $name = $pr["product_name"];
        $price = $pr["retail_price"];
        $prod_desc = $pr["product_desc"];
        $prod_shortdesc = $pr["short_desc"];
        $metatitle = $pr["product_name"];
        $metaKeyword = $pr["product_name"];
        $metaDesc = $pr["product_desc"];
        $qty = $pr["qty_on_hand"];
        $image_url = $pr["image_url"];

        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        try {
            //echo("Searching for Product {$sku}<br/>");
            $product = $this->_productRepository->get($sku, false, null, false);
            //echo "{$sku}  Found - UPDATE: " . $product->getId();
            $this->UpdateProductInfo($product, $pr);

        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            //echo "{$sku} Not Found - CREATE ";
            //$this->insertProduct($sku, $name, $price, $metatitle, $metaKeyword, $metaDesc, $qty, $prod_desc, $prod_shortdesc, $image_url);
            $this->insertProduct($pr);
        }
    }

    public function insertProduct($pr)
    {
        $this->logger->info(__METHOD__);


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
            $newProduct->setDescription($pr['product_desc']);
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
            $this->logger->error("~ERR~ Saving New Product {$pr['sku']}  : Message : {$ex->getMessage()}");        }

        if (file_exists($pr['image_url'])){
            $mySaveDir = $this->_dir->getPath('media');
            $filename = basename($pr['image_url']);
            $completeSaveLoc = $mySaveDir. '/' . $filename;
            if(!file_exists($completeSaveLoc)) {
                try {
                    file_put_contents($completeSaveLoc, file_get_contents($pr['image_url']));
                } catch (Exception $e) {
                    $this->logger->error("~ERR~ Saving {$filename} to media folder : Message : {$e->getMessage()}");
                }
            }

            try {
                $newProduct->addImageToMediaGallery($completeSaveLoc, array('image','thumbnail','small_image'), true, false);
                $newProduct->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage());
                $this->logger->info("~ERR~ Saving IMAGES to Product {$pr['sku']} : Message : {$e->getMessage()}");
                echo "ERROR Saving IMAGES to Product Message : {$e->getMessage()} ";
            }

        } else {
            //No Image is available for this product
            echo "<h3><b><i>Image for sku {$pr['sku']} does not exist on source {$pr['image_url']}</i></b></h3>";
            $this->logger->info("~ERR~ Image for sku {$pr['sku']} does not exist on source {$pr['image_url']}");
        }

        $this->DisplayProductInfo($newProduct->getId(), "<b>INSERT</b>", $pr['sku'], $pr['product_desc'], $pr['retail_price']);

    }

    public function DisplayProductInfo($id, $action, $sku, $desc, $price){
        echo $action . " ID: " . $id ." product sku : " . $sku . " desc : " . $desc . " price : " . $price . "<br>";
        $this->logger->info("{$action} ID: {$id}, Product SKU : {$sku}, Desc : {$desc}, Price : {$price}");
    }


    public function UpdateProductInfo($product, $pr){

        $this->logger->info(__METHOD__);

        //Determine the Category IDs for this product
        $categories = $this->GetProductCategories($pr);
        $this->logger-info("Product {$pr['sku']} : Categories {$categories}");

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


        $product->setTaxClassId(0); // (0 - none, 1 - default, 2 - taxable, 4 - shipping)
        $product->setVisibility(4); // catalog and search visibility
        $product->setColor(24);
        $product->setPrice($pr['retail_price']);
        $product->setCost(1);
        $product->setMetaTitle($pr['product_name']);
        $product->setMetaKeyword($pr['product_name']);
        $product->setMetaDescription($pr['product_desc']);
        $product->setDescription($pr['product_desc']);
        $product->setShortDescription($pr['short_desc']);

        $product->setStockData(
            [
                'use_config_manage_stock' => 0,
                'manage_stock' => 1, // manage stock
                'min_sale_qty' => 1, // Shopping Cart Minimum Qty Allowed
                'max_sale_qty' => 2, // Shopping Cart Maximum Qty Allowed
                'is_in_stock' => 1, // Stock Availability of product
                'qty' => (int)$product['qty_on_hand']
            ]
        );

        $product->save();



        //Check if the image exists on the url provided
        //echo '<hr><a href="'. $pr['image_url'] . '"> ' . '<img src="'. $pr['image_url'] . '" alt="'. $pr['image_url'] . '">' . '</a>';
        if (file_exists($pr['image_url'])) {

            //echo '<a href="'. $pr['image_url'] . '"> ' . '<img src="'. $pr['image_url'] . '" alt="'. $pr['image_url'] . '">' . '>';


            //Clear exisitng media galliers
            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();

            foreach ($existingMediaGalleryEntries as $key => $entry) {
                //We can add your condition here
                unset($existingMediaGalleryEntries[$key]);
                $this->logger->info("~INFO~ Image Removed : {$key}");
                //echo('Removing Medial Gallery' . $key . '<br>');
            }

            $mySaveDir = $this->_dir->getPath('media');
            //echo "<hr>SaveDir : " .  $mySaveDir;
            //echo '<img src="'. $pr['image_url'] .'" alt="' . $pr['sku'] . '">';

            $filename = basename($pr['image_url']);
            //echo '<br> filename: ' . $filename;

            $completeSaveLoc = $mySaveDir . '/' . $filename;
            if (!file_exists($completeSaveLoc)) {
                try {
                    file_put_contents($completeSaveLoc, file_get_contents($pr['image_url']));
                    //move uploaded file
                } catch (Exception $e) {
                    $this->logger->error("~ERR~ Image file {$filename} could not be saved to media. {$e->getMessage()}.");
                }
            } else {
                $this->logger->error("~ERR~ Image file {$filename} Does not exist");
            }
            try {
                $product->addImageToMediaGallery($completeSaveLoc, array('image', 'thumbnail', 'small_image'), true, false);
                $product->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage());
                $this->logger->error("~ERR~ Cant Add Image {$filename} to media gallery:  {$e->getMessage()}.");
            }
        } else {
            //No Image is available for this product
            $this->logger->error("~ERR~ mage for sku {$pr['sku']} does not exist on source {$pr['image_url']}.");
            //echo "<h3><b><i>Image for sku {$pr['sku']} does not exist on source {$pr['image_url']}</i></b></h3>";
        }

        $this->DisplayProductInfo($product->getId(), "UPDATE", $pr['sku'], $pr['product_desc'], $pr['retail_price']);

    }


    public function GetProductCategories($pr){
        $this->logger->info(__METHOD__);
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

        $brandName = $pr['brand_name'];
        if($pr['brand_name']!="") {
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

        $this->logger->info(__METHOD__);
        $this->logger->info("CategoryName : {$CategoryName}, Parent Id : {$parentId}");

        try{

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

                $this->logger->info("Parent Category Path : {$parentCategory->getPath()}");

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
                $this->logger->info("Please enter valid category name.");
            }
        } catch (\Exception $ex){
            $this->logger->error("~ERR~ Error saving Category {$category} : Messge ; {$ex->getMessage()}");
        }
        //echo "<br>SAVE CATEGORY FUCTION : Category Name {$CategoryName} ... Parent {$parentId}";

    }


}
