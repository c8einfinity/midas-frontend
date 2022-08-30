<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_PartFinder
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\PartFinder\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Message\ManagerInterface;
use Webkul\PartFinder\Api\Data\PartfinderInterface;

class AdminProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     *
     * @param ManagerInterface $messageManager
     * @param \Webkul\PartFinder\Api\Data\PartfinderInterfaceFactory $finderFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\PartFinder\Model\Partfinder\PartfinderDropdownFactory $dropdownFactory
     * @param \Webkul\PartFinder\Api\Data\DropdownOptionInterfaceFactory $optionFactory
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        ManagerInterface $messageManager,
        \Webkul\PartFinder\Api\Data\PartfinderInterfaceFactory $finderFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\PartFinder\Model\Partfinder\PartfinderDropdownFactory $dropdownFactory,
        \Webkul\PartFinder\Api\Data\DropdownOptionInterfaceFactory $optionFactory,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_messageManager = $messageManager;
        $this->finderFactory = $finderFactory;
        $this->dropdownFactory = $dropdownFactory;
        $this->optionFactory = $optionFactory;
        $this->_request = $request;
        $this->_date = $date;
    }

    /**
     * product save after
     *
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $partFinder = $this->finderFactory->create();
        if ($id = $this->_request->getParam('finder_id')) {
            $partFinder->load($id);
            $dropdownData = json_decode($this->_request->getParam('dropdown_data'), true);
            $dropdowns = [];
            foreach ($dropdownData as $dropdown) {
                $dropdownModel = $this->dropdownFactory->create()->load($dropdown['dropdown_id']);
                $options = [];
                foreach ($dropdown['options'] as $option) {
                    if (!isset($option['is_new'])) {
                        $optionModel = $this->optionFactory->create()->load($option['option_id']);
                    } else {
                        $option['value'] = $option['id'];
                        unset($option['id']);
                        unset($option['option_id']);
                        unset($option['is_new']);
                        $option['dropdown_id'] = $dropdown['dropdown_id'];
                        $optionModel = $this->optionFactory->create()->setData($option);
                    }
                    $options[] = $optionModel;
                }
                $dropdownModel->setOptions($options);
                $dropdowns[] = $dropdownModel;
            }
            $partFinder->setDropdowns($dropdowns);
            $partFinder->save();
            $this->processProducts($partFinder, $product);
        }
    }

    /**
     * save product variation
     *
     * @param PartfinderInterface $partFinder
     * @return void
     */
    private function processProducts(
        PartfinderInterface $partFinder,
        \Magento\Catalog\Model\Product $product
    ) {
        if ($this->_request->getParam('finder_variation')) {
            $variations = json_decode($this->_request->getParam('finder_variation'), true);
            $productsData = [];
            foreach ($variations as $variation) {
                $productsData[] = [
                    'product_id' => $product->getId(),
                    'variationKey' => implode('-', $variation)
                ];
            }
            $partFinder->productEditSave($productsData);
        }
    }
}
