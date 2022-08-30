<?php
namespace C8EEE\Parcelninja\Gateway\Parcelninja\Endpoints;
use C8EEE\Parcelninja\Gateway\Parcelninja\EndpointInterface;
use \Magento\Framework\DataObject;
use \Magento\Framework\Json\Helper\Data;


class GetQuotes extends DataObject implements EndpointInterface {

	protected $_endpoint = 'quote';

	protected $logger;

	protected $scopeConfigObject;

	/**
     * @var Helper
     */
    protected $helperData;

	/**
	 *
	 * @var \Magento\Framework\Json\Helper\Data
	 */
	protected $jsonHelper;

	public function __construct(
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\Magento\Framework\Json\Helper\Data $jsonHelper,
			\C8EEE\Parcelninja\Helper\Data $helperData,
			array $data = []
	) {
		$this->jsonHelper = $jsonHelper;
		$this->helperData = $helperData;
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

		$allowedMethods = explode(',', $this->helperData->getAdminField('shippingtypes'));
		$jsonDecode = $this->jsonHelper->jsonDecode($result);
		$resultAfterFilter = array();

		foreach ($jsonDecode as $key => $value) {
			if(isset($value['service']) && !in_array($value['service']['code'] , $allowedMethods) ){
                continue;
            }
            $resultAfterFilter[] = $value;
		}

		$result = $this->jsonHelper->jsonEncode($resultAfterFilter);

		return $result;
	}

}
