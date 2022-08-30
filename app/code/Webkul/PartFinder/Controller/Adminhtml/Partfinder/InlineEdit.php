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
namespace Webkul\PartFinder\Controller\Adminhtml\Partfinder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\PartFinder\Model\PartfinderFactory;
use Webkul\PartFinder\Helper\Data;

class InlineEdit extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Webkul\PartFinder\Model\PartfinderFactory
     */
    protected $partfinderFactory;

    /**
     * @var \Webkul\PartFinder\Helper\Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param PartfinderFactory $partfinderFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        PartfinderFactory $partfinderFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->partfinderFactory = $partfinderFactory;
        $this->helper = $helper;
    }

    /**
     * Inline edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        
        if ($this->getRequest()->getParam('isAjax')) {
            $partFinderItems = $this->getRequest()->getParam('items', []);
            if (empty($partFinderItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($partFinderItems) as $partFinderId) {
                    $partFinderModel = $this->partfinderFactory->create();
                    $partFinder = $this->helper->loadObjectById($partFinderModel, $partFinderId);

                    try {
                        $partFinder->setData(
                            $this->arrayMerge(
                                $partFinder->getData(),
                                $partFinderItems[$partFinderId]
                            )
                        );
                        $this->helper->saveObject($partFinder);
                    } catch (\Exception $e) {
                        $messages[] = "[Partfinder ID: {$partFinderId}]  {$e->getMessage()}";
                        $error = true;
                    }
                }
            }
        }
        
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     *  Merge Twp Array
     *
     * @params array  $firstArray
     * @params array $secoundArray
     *
     * @return array
     */
    public function arrayMerge($firstArray, $secoundArray)
    {
        return array_merge($firstArray, $secoundArray);
    }
}
