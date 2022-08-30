<?php
/**
 * Motus
 */

namespace Motus\Quotesystem\Plugin\CatalogWidget\Block\Product;

class ProductsList
{

    protected $quoteHelper;

    private $_productInfo;

    protected $_registry;

    /**
     * @param \Motus\Quotesystem\Helper\Data $quoteHelper
     */
    public function __construct(
        \Motus\Quotesystem\Helper\Data $quoteHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory

    ) {
        $this->quoteHelper = $quoteHelper;
        $this->repository = $productRepository;
        $this->registry = $registry;
        $this->_productFactory = $productFactory;
    }

    public function afterGetProductPriceHtml(
        \Magento\CatalogWidget\Block\Product\ProductsList $subject,
        $result
    ) {
        $showPrice = (int)$this->quoteHelper->getConfigShowPrice();
        $id = $this->_productInfo->getEntityId();
        $quoteStatus = $this->repository->getById($id)->getQuoteStatus();
        $modStatus = $this->quoteHelper->getModuleStatus();
        $product = $this->_productFactory->create()->load($id);
        
        $cats = $product->getCategoryIds();
        if (in_array("1025", $cats)) {
            $modStatus = 0;
            $showPrice = 1;
            $quoteStatus = 0;
        }

        if (($quoteStatus == 1)) {
            $productInfo = $this->getQuoteProduct($id);
            $quoteItems = $this->registry->registry("quoteitems");
            if ($quoteItems) {
                $tmpArray = $quoteItems[0]+$productInfo;
                $quoteItems = [];
                array_push($quoteItems, $tmpArray);
                $this->registry->unregister("quoteitems");
                $this->registry->register("quoteitems", $quoteItems);
            } else {
                $tmpArray = [];
                array_push($tmpArray, $productInfo);
                $this->registry->register("quoteitems", $tmpArray);
            }
        }

        if ($modStatus && !$showPrice) {

                $result = $this->quoteHelper->removePriceInfo($result);
        }
        
        return $result;
    }

        /**
         * get quoted product
         *
         * @param int
         *
         * @return array
         */
    public function getQuoteProduct($id)
    {
        $auctionModuleEnabledOrNot = $this->quoteHelper->checkModuleIsEnabledOrNot('Motus_Auction');
        $quoteProductsInfo = [];
        $productData = $this->repository->getById($id);
        $auctionCheck = 1;
        if ($auctionModuleEnabledOrNot) {
            $auctionValues = $productData->getAuctionType();
            $auctionOpt = explode(',', $auctionValues);
            if (in_array(2, $auctionOpt)) {
                $auctionCheck = 0;
            }
        }
        if ($auctionCheck) {
            $productUrl = $productData->getUrlModel()->getUrl($productData, ['_ignore_category' => true]);
            
            if (!$productData->getTypeInstance()->isPossibleBuyFromList($productData)) {
                $quoteProductsInfo[$productData->getId()]['url'] = $productUrl;
                $quoteProductsInfo[$productData->getId()]['status'] = 0;
            } else {
                $minqty = $productData->getMinQuoteQty();
                if ($minqty=='' || $minqty==null) {
                    $minqty = $this->quoteHelper->getConfigMinQty();
                }
                $quoteProductsInfo[$productData->getId()]['min_qty'] = $minqty;
                $quoteProductsInfo[$productData->getId()]['url'] = $productUrl;
                $quoteProductsInfo[$productData->getId()]['status'] = 1;
            }
        }
        return $quoteProductsInfo;
    }

    /**
     * beforeGetProductPrice plugin to assign the product model to a variable
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function beforeGetProductPriceHtml(
        \Magento\CatalogWidget\Block\Product\ProductsList $listProduct,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->_productInfo = $product ;
    }
}
