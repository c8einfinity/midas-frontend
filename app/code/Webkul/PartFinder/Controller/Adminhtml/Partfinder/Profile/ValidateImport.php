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

class ValidateImport extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data as JsonHelper
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
            if (isset($postData['import_file']) && empty($postData['import_file'])) {
                return $resultJson->setData([
                    'error' => true,
                    'message' => __('Import file not found.')
                ]);
            }
            if (isset($postData['dropdowns']) && $postData['dropdowns'] == '') {
                return $resultJson->setData([
                    'error' => true,
                    'message' => __('Profile dropdowns are not exist.')
                ]);
            }
            $dropdowns = json_decode($postData['dropdowns'], true);
            $dropdownData = [];
            foreach ($dropdowns as $dropdown) {
                $dropdownData[] = $dropdown['column_name'];
            }
            $this->helper->processCsv($postData['import_file'][0]);
            $validateError = $this->helper->validate($dropdownData);
            
            if ($validateError['error']) {
                if ($validateError['error'] == 1) {
                    $message = __(
                        'Column(s) "%1" not found in uploaded file.',
                        $validateError['columns']
                    );
                } else {
                    $message = __(
                        'Column(s) "%1" not found in profiler.',
                        $validateError['columns']
                    );
                }
                return $resultJson->setData([
                    'error' => true,
                    'message' => $message
                ]);
            }
            return $resultJson->setData([
                'error' => false,
                'message' => __('Import file found.')
            ]);
        }
    }
}
