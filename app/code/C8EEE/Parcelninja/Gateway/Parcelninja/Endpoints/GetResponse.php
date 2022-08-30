<?php
namespace C8EEE\Parcelninja\Gateway\Parcelninja\Endpoints;
use C8EEE\Parcelninja\Gateway\Parcelninja\EndpointInterface;
use \Magento\Framework\DataObject;
use \Magento\Framework\Json\Helper\Data;


class GetResponse extends DataObject implements EndpointInterface {

	protected $_endpoint = 'quote';

	protected $logger;

	protected $scopeConfigObject;

	/**
	 *
	 * @var \Magento\Framework\Json\Helper\Data
	 */
	protected $jsonHelper;

	public function __construct(
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\Magento\Framework\Json\Helper\Data $jsonHelper,
			array $data = []
	) {
		$this->jsonHelper = $jsonHelper;
		$this->scopeConfigObject = $scopeConfig;
		$data['endpoint'] = $this->_endpoint;
		parent::__construct($data);
	}

	public function makeBody($params = []) {
		return $params;
	}

	public function makeRequestHeaders($parameters = []) {
		return [
				'Content-Type' => 'application/json',
				'Accept' => '*/*',
				'Accept-Encoding' => 'gzip, deflate',
				'User-Agent' => 'runscope/0.1'
		];

	}

	public function filterResult($result){
		return $result;
	}

}
