<?php

namespace C8EEE\Parcelninja\Gateway\Parcelninja;

use \Magento\Framework\HTTP\ZendClientFactory;
use \C8EEE\Parcelninja\Gateway\Parcelninja\Endpoints\GetQuotes;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\HTTP\ZendClient;
use \Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use \C8EEE\Parcelninja\Helper\Data as SmsHelper;

class Client extends \Magento\Framework\DataObject{
	/**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigObject;

    /**
     *
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $scopeConfigWriter;

    /**
     * @var \C8EEE\Sms\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory $clientFactory
     */
    protected $clientFactory;

    /**
     *
     * @var \C8EEE\Sms\Model\Api\EndpointFactory
     */
    protected $endpointFactory;

     /**
      * @var \C8EEE\Sms\Helper\Data
      */
     protected $_helper;

     /**
      * @var \C8EEE\Sms\Helper\Data
      */
     protected $curlClient;

    /**
     * @var Json Serializer
     */
    protected $json;


	public function __construct(
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\Magento\Framework\HTTP\ZendClientFactory $clientFactory,
			EndpointFactory $endpointFactory,
			ConfigInterface $scopeWriter,
			\Magento\Framework\HTTP\Client\Curl $curl,
			\C8EEE\Parcelninja\Helper\Data $helper,
            \Magento\Framework\Serialize\Serializer\Json $json,
			array $data = []
	) {
            $this->json = $json;
			$this->_helper = $helper;
			$this->scopeConfigWriter = $scopeWriter;
			$this->scopeConfigObject = $scopeConfig;
			$this->curlClient = $curl;
			$this->clientFactory = $clientFactory;
			$this->endpointFactory = $endpointFactory;
			parent::__construct($data);
	}

	public function makeRequest($endpoint, $method , $payload = []){

        try {

            if (!empty($payload)) {
                $requestedEndpoint = $this->endpointFactory->create($endpoint, []);

                $url = $this->_helper->getEndpointUrl($requestedEndpoint->getData('endpoint'));
            } else {
                $url = $this->_helper->getEndpointUrl($endpoint);
            }

            /** @var \Magento\Framework\HTTP\ZendClient $client */
            $client = $this->clientFactory->create();

            $clientConfig = ['verifypeer' => FALSE];
            $client->setConfig($clientConfig);

            switch ($method) {
                case ZendClient::POST:
                    $parameters = $requestedEndpoint->makeBody($payload);
                    $jsonRawData = $this->json->serialize($parameters);
                    // Logging Payload
                    $this->_helper->debug("Payload:", $jsonRawData);

                    $client->setRawData($jsonRawData, 'application/json');
                break;
            }

            $client->setHeaders(['Authorization' => "Bearer ".$this->_helper->getApiKey()]);
            $client->setUri($url);
            $client->setMethod($method);

            $response = $client->request($method)->getBody();

            return $response;

        } catch (Exception $e) {
            $this->_helper->debug("Payload:", $e->getMessage());

            return [];
        }

	}

}
