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
namespace Webkul\PartFinder\Controller\Adminhtml\Partfinder\Profile;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\PartFinder\Helper\Data;

class Process extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Webkul\PartFinder\Helper\Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->helper = $helper;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        if ($this->getRequest()->isAjax()) {
            $offset = $this->getRequest()->getParam('number', 0);
            $previousDropdowns= $this->getRequest()->getParam('dropdown');
            $error= $this->getRequest()->getParam('error', 0);
            list($count, $message, $result, $dropdowns, $products) = $this->helper->startImporting(
                $offset,
                $error,
                $previousDropdowns
            );
            
            return $resultJson->setData([
                'result' => $result,
                'message' => $message,
                'count' => $count,
                'dropdowns' => $dropdowns,
                'products' => $products
            ]);
        }
    }
}
