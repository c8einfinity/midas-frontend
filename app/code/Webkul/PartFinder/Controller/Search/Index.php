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
namespace Webkul\PartFinder\Controller\Search;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\PartFinder\Model\FinderQueryFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Webkul\PartFinder\Model\FinderQueryFactory
     */
    protected $finderQueryFactory;
    
    /**
     * @param Context $context
     * @param Resolver $layerResolver
     * @param StoreManagerInterface $storeManager
     * @param FinderQueryFactory $finderQueryFactory
     */
    public function __construct(
        Context $context,
        Resolver $layerResolver,
        StoreManagerInterface $storeManager,
        FinderQueryFactory $finderQueryFactory
    ) {
        parent::__construct($context);
        $this->layerResolver = $layerResolver;
        $this->storeManager = $storeManager;
        $this->finderQueryFactory = $finderQueryFactory;
    }

    /**
     * Display search result
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);

        $finderQuery = $this->finderQueryFactory->get();

        $storeId = $this->storeManager->getStore()->getId();
        $finderQuery->setStoreId($storeId);

        $finderQuery = $finderQuery->getQueryText();
        
        if ($finderQuery != '') {
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } else {
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
        }
    }
}
