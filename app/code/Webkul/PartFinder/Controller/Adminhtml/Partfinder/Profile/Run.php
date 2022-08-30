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
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Webkul\PartFinder\Helper\Data;

class Run extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Webkul\PartFinder\Helper\Data
     */
    protected $helper;
    
    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param JsonHelper $jsonHelper
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        JsonHelper $jsonHelper,
        Data $helper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->jsonHelper = $jsonHelper;
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
            $pattern = '/({.+})/i';
            preg_match($pattern, $this->getRequest()->getContent(), $data);
            $postData = $this->jsonHelper->jsonDecode($data[0], true);
            $this->helper->processCsv($postData['data']['import_file'][0]);
            $this->helper->processDropdowns($postData['data']['dropdowns']);
            $this->helper->saveProfileData();
        }
        return $resultJson->setData(['result' => $this->helper->getTotalRows()]);
    }
}
