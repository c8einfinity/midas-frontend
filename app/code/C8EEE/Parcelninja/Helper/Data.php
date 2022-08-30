<?php
/**
 * Copyright © 2020 C8EEE. All rights reserved.
 */
namespace C8EEE\Parcelninja\Helper;

/**
 * Class Helper Store Configuration
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	const API_URL = "https://optimise.parcelninja.com/api/v2/";

	const API_KEY = "carriers/c8eee_parcelninja/api_token";

	const WAREHOUSE_METHOD_CODE = "S0";

	const DIMENSIONS = [
		'length' => "carriers/c8eee_parcelninja/dim_length",
		'width'	 => "carriers/c8eee_parcelninja/dim_width",
		'height' => "carriers/c8eee_parcelninja/dim_height",
		'weight' => "carriers/c8eee_parcelninja/def_weight"
	];

	const DELIVERY_METHOD = [
		'normal'	=> "Normal",
		'cheapest'	=> "Cheapest",
		'fastest' 	=> "Fastest"
	];


	protected $_encryptor;

	protected $_log;


	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\C8EEE\Parcelninja\Logger\Logger $logger,
		\Magento\Framework\Encryption\EncryptorInterface $encryptor
	)
	{
		$this->_log		  = $logger;
		$this->_encryptor = $encryptor;
		parent::__construct($context);
	}


	public function getApiUrl($store = null){

		return self::API_URL;

	}


	public function getApiKey($store = null){

		$encryptedValue = $this->scopeConfig->getValue(
            self::API_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

		return $this->_encryptor->decrypt($encryptedValue);

	}

	public function getDimension($store = null){

		$dimensions = [];

		foreach (self::DIMENSIONS as $key => $value) {
			$dimensions[$key] = (int)$this->scopeConfig->getValue(
						            self::DIMENSIONS[$key],
						            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
						            $store
						        );
		}
		return $dimensions;
	}

	public function getEndpointUrl($endpoint){
		return $this->getApiUrl().$endpoint;
	}

	public function getAdminField($key)
    {
        $value = $this->scopeConfig->getValue('carriers/c8eee_parcelninja/' . $key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $value;
    }

	public function debug($message, $data = NULL)
    {
        if ($this->getAdminField('debug')) {
            $this->_log->debug($message . print_r($data, TRUE));
        }
    }

    public function getDeliveryMethod($key)
    {
    	return self::DELIVERY_METHOD[$key];
    }

    public function getShowDeliveryDate() {
        return $this->getAdminField('show_delivery_date');
    }

}
