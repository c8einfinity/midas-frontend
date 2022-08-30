<?php

namespace C8EEE\Parcelninja\Gateway\Parcelninja;
use \C8EEE\Parcelninja\Gateway\Parcelninja\Client;
use \C8EEE\Parcelninja\Helper\Data;
use \Magento\Framework\DataObject;
use \Magento\Framework\HTTP\ZendClient;
use Magento\Quote\Model\Quote\Address\RateRequest;


class Request extends DataObject{

	/**
	 * HTTP Client
	 * @var client
	 */
	protected $client;

	/**
	 * Helper
	 * @var \C8EEE\Parcelninja\Helper\Data
	 */
	protected $helper;

	/**
	 *
	 * @param \C8EEE\Parcelninja\Model\Gateway\Parcelninja\Api\Client $client
	 * @param \C8EEE\Parcelninja\Logger\Logger $logger
	 */
	public function __construct(
		Client $client,
		Data $helper
	) {
		$this->client = $client;
		$this->helper = $helper;
	}

	public function sendRequest($endpoint , $payload = []){

		try {
            if (empty($payload)) {
                $response = $this->client->makeRequest($endpoint, ZendClient::GET);
            } else {
                $response = $this->client->makeRequest($endpoint, ZendClient::POST, $payload);
            }
		} catch (Exception $e) {
			$this->helper->debug("Exception", $e->getMessage());

			return [];
		}
		return $response;
	}



}
