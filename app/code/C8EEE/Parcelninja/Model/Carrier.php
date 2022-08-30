<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace C8EEE\Parcelninja\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Config;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Catalog\Model\Product\Type;
use C8EEE\Parcelninja\Model\GatewayFactory;


/**
 * Class Carrier In-Store Pickup shipping model
 */
class Carrier extends AbstractCarrier implements CarrierInterface
{

    /**
     * Rate result data
     *
     * @var Result|null
     */
    protected $_result;

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'c8eee_parcelninja';

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var request
     */
    protected $_request;

    /**
     * @var MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @var Helper
     */
    protected $helperData;

    /**
     * @var payload
     */
    protected $payload = [];

    /**
     * @var GatewayFactory
     */
    protected $shippingGateway;

    /**
     * @var Json Serializer
     */
    protected $json;

    protected $date;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        \C8EEE\Parcelninja\Helper\Data $helperData,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        GatewayFactory $shippingGateway,
        \Magento\Framework\Serialize\Serializer\Json $json,
        array $data = []
    ) {
        $this->helperData        = $helperData;
        $this->json              = $json;
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->shippingGateway   = $shippingGateway;
        $this->date = $date;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Generates list of allowed carrier`s shipping methods
     * Displays on cart price rules page
     *
     * @return array
     * @api
     */
    public function getAllowedMethods()
    {
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
    }

    /**
     * Collect and get rates for storefront
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param RateRequest $request
     * @return DataObject|bool|null
     * @api
     */
    public function collectRates(RateRequest $request)
    {
        /**
         * Make sure that Shipping method is enabled
         */
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $this->_request = clone $request;

        $gatewayObject = $this->shippingGateway->create(\C8EEE\Parcelninja\Gateway\Parcelninja::class, []);

        $request = $gatewayObject->getQuotes($this->_request);

        if (!is_array($request)) {
            $result = json_decode($request);
        }


        if (!empty($result->quotes)) {

            return $this->getRateResult($result->quotes);
        } else {
            return false;
        }

    }

    public function getRateResult( $rateResult){
        if(isset($rateResult['message'])){
            return [];
        }

        $result = $this->rateResultFactory->create();


        $show_delivery_date = $this->helperData->getShowDeliveryDate();
        $date = $this->date->date('Y-m-d');
        foreach ($rateResult as $rate) {
            $rate = (array)$rate;
			//skip method if not set
			if(!isset($rate['courier'])){continue;}
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->rateMethodFactory->create();
            $method->setCarrier($this->_code);
            $delivery_method = $this->helperData->getDeliveryMethod($this->getConfigData('delivery_method'));

            //$debugMode =  $this->helperData->getDeliveryMethod($this->getConfigData('debug'));

            $title = $this->getConfigData('title');

            //$title .= $rate['quoteId'];

            if($show_delivery_date) {
              $dateExplode = explode("T", $rate["deliveryDate"])[0];

              $title = "Expected Delivery Date: ".$dateExplode;
            }

            $method->setCarrierTitle($title);
            $method->setMethod($rate['courier']);
            $method->setMethodTitle("ParcelNinja Optimise");
            $amount = $rate['price'];
            $method->setPrice($amount);
            $method->setCost($amount);
            $method->setData('pn_cost',$amount);
            $method->setData('pn_quote_id',$rate['quoteId']);
            $result->append($method);
            break;
        }
        return $result;
    }

    /**
     * Get configured Store Shipping Origin
     *
     * @return array
     */
    protected function getShippingOrigin()
    {
        /**
         * Get Shipping origin data from store scope config
         * Displays data on storefront
         */
        return [
            'country_id' => $this->_scopeConfig->getValue(
                Config::XML_PATH_ORIGIN_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $this->getData('store')
            ),
            'region_id' => $this->_scopeConfig->getValue(
                Config::XML_PATH_ORIGIN_REGION_ID,
                ScopeInterface::SCOPE_STORE,
                $this->getData('store')
            ),
            'postcode' => $this->_scopeConfig->getValue(
                Config::XML_PATH_ORIGIN_POSTCODE,
                ScopeInterface::SCOPE_STORE,
                $this->getData('store')
            ),
            'city' => $this->_scopeConfig->getValue(
                Config::XML_PATH_ORIGIN_CITY,
                ScopeInterface::SCOPE_STORE,
                $this->getData('store')
            )
        ];
    }
}
