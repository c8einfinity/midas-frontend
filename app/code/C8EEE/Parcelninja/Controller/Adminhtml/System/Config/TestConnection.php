<?php

namespace C8EEE\Parcelninja\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use C8EEE\Parcelninja\Helper\Data;
use C8EEE\Parcelninja\Gateway\Parcelninja;

class TestConnection extends Action
{
    protected $parcelninja;

    protected $resultJsonFactory;

    /**
     * @var Data
     */
    protected $helper;

    protected $json;

    protected $decoder;

    protected $helperData;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Data $helper,
        Parcelninja $parcelninja,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Json\Decoder $decoder,
        \C8EEE\Parcelninja\Helper\Data $helperData
    )
    {
        $this->resultJsonFactory    = $resultJsonFactory;
        $this->helper               = $helper;
        $this->parcelninja          = $parcelninja;
        $this->json                 = $json;
        $this->decoder              = $decoder;
        $this->helperData           = $helperData;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $response = $this->parcelninja->getResponse();
        if (!empty($response)) {
            $decoded_response = $this->decoder->decode($response);
            $this->helperData->debug("response", $decoded_response);
        } else {
            $decoded_response['message'] = "failed";
        }

        if(isset($decoded_response['message'])){
            return $result->setData(['status' => 401,'statusText'=>'Authorization has been denied for this request.']);
        }
        else{
            return $result->setData(['status' => 200]);
        }
    }

    /**
     * Return product relation singleton
     *
     * @return \C8EEE\Parcelninja\Model\Relation
     */
    protected function _getSyncSingleton()
    {
        return $this->_objectManager->get('C8EEE\Parcelninja\Model\Relation');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('C8EEE_Parcelninja::config');
    }
}
?>
