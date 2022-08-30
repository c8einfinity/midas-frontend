<?php

namespace C8EEE\Parcelninja\Gateway;

use C8EEE\Parcelninja\Gateway\Parcelninja\Request as ParcelninjaRequest;
use C8EEE\Parcelninja\Helper\Data;
use C8EEE\Parcelninja\Model\GatewayInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Checkout\Model\Cart;
use Magento\Quote\Model\Quote\Address\RateRequest;


class Parcelninja extends AbstractGateway implements GatewayInterface
{

    const DELIVERY_METHOD = "carriers/c8eee_parcelninja/delivery_method";

    protected $_request;

    protected $ParcelninjaRequest;

    protected $helperData;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    protected $addressInterface;

    protected $scopeConfigObject;

    private $cart;

    public function __construct(
        ParcelninjaRequest                                          $ParcelninjaRequest,
        Data                                                        $helperData,
        \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Quote\Api\Data\AddressInterface                    $addressInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig,
        Cart                                                       $cart
    )
    {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->helperData = $helperData;
        $this->ParcelninjaRequest = $ParcelninjaRequest;
        $this->addressInterface = $addressInterface;
        $this->scopeConfigObject = $scopeConfig;
        $this->cart = $cart;
    }


    public function getDeliveryMethod($store = null)
    {

        return $this->scopeConfigObject->getValue(
            self::DELIVERY_METHOD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

    }

    /**
     * Returns array of Quotes for Shipping
     * @return array
     */
    public function getQuotes(RateRequest $request)
    {

        $this->_request = $request;

        $shippingInformation = $this->_getShippingInformation();
        $deliveryMethod = $this->getDeliveryMethod();

	
	file_put_contents("./request.log", "SHIPPING:\n".print_r($shippingInformation,1)."\n", FILE_APPEND);


        if (is_null($shippingInformation['postalCode'])) {
            $quotes = [];
        } else {

                $quoteInformation = [
                    'reference' => "Order".$this->cart->getQuote()->getId(),
                    'deliveryType' => 'All',
                    'dispatchDate' => date("Y-m-d")."T".date("H:i:s.ss")."Z", // "2021-11-11T20:03:52.999Z",
                    'destination' => $this->_getShippingInformation(),
                    'parcels' => $this->_getAllItems(),
                    'quoteType' => "All",
                    'service' => 'S0',
                    'waybillNo' => $this->cart->getQuote()->getId(),
                    'clientIdentifier' => $this->_request->getStoreId().""
                ];


		file_put_contents("./request.log", "QUOTEINFORMATION:\n".print_r ($quoteInformation, 1)."\n", FILE_APPEND);

                $quotes = $this->ParcelninjaRequest->sendRequest(
                    \C8EEE\Parcelninja\Gateway\Parcelninja\Endpoints\GetQuotes::class
                    ,
                    $quoteInformation
		);

		file_put_contents("./request.log", "QUOTES:\n".print_r($quotes,1)."\n", FILE_APPEND);


        }

        return $quotes;

    }

    /**
     * Returns array of Response for Testing
     * @return array
     */
    public function getResponse()
    {

        $response = $this->ParcelninjaRequest->sendRequest(
            'address/postalcode/7570'
        );
        return $response;

    }

    /**
     * Returns array of shipping Information
     * @return array
     */
    protected function _getShippingInformation()
    {

	file_put_contents ("./request.log", "ADDRESS:\n".'address/postalCode/'.$this->_request->getDestPostcode(), FILE_APPEND);    
	    
	$where = json_decode($this->ParcelninjaRequest->sendRequest('address/postalCode/'.$this->_request->getDestPostcode()));

	file_put_contents ("./request.log", "SHIPPINGINFORMATION REQUEST:\n".print_r ($where, 1), FILE_APPEND);

	if (count($where) > 0) {
		$addressInfo = $where[0];	
	}

        $shippingInformation = [
            'contactName' => 'None',
            'contactNumber' =>  '0123456789',
            'contactEmail' => 'test@test.com',
            'companyName' => 'string',
            'addressLine1' => 'string',
            'addressLine2' => 'string',
            'city'  => $addressInfo->city,
            'province' => $addressInfo->province,
            'pickupPointId' => 'string',
            'postalCode' => $this->_request->getDestPostcode(),
            'suburb' => $addressInfo->suburb
        ];

        return $shippingInformation;
    }

    /**
     * Prepare items to pieces
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    protected function _getAllItems()
    {
        $allItems = $this->_request->getAllItems();
        $fullItems = [];

        foreach ($allItems as $item) {

            if ($item->getProductType() == Type::TYPE_BUNDLE && $item->getProduct()->getShipmentType()) {
                continue;
            }

            $qty = $item->getQty();
            $changeQty = true;
            $checkWeight = true;
            $decimalItems = [];

            if ($item->getParentItem()) {
                if (!$item->getParentItem()->getProduct()->getShipmentType()) {
                    continue;
                }
                if ($item->getIsQtyDecimal()) {
                    $qty = $item->getParentItem()->getQty();
                } else {
                    $qty = $item->getParentItem()->getQty() * $item->getQty();
                }
            }

	    $dimensions = $this->helperData->getDimension();


            $dimensions['weight'] = ($item->getWeight()  > 0) ? $item->getWeight() * 1000 : $dimensions['weight'];
            $dimensions['width'] = ($item->getWidth() > 0) ? $item->getWidth() * 10 : $dimensions['width'];
            $dimensions['height'] = ($item->getHeight() > 0) ? $item->getHeight() * 10 : $dimensions['height'];
            $dimensions['length'] = ($item->getLength() > 0) ? $item->getLength() * 10 : $dimensions['length'];

            //$dimensions['weight'] = ($item->getData('row_weight') > 1) ? $item->getData('row_weight') : $dimensions['weight'];

            $temparray = [
                "description" => $item->getSku(),
                "qty" => $qty,
                "length" => $dimensions["length"] = 0 ? 10 : $dimensions["length"],
                "width" => $dimensions["width"] = 0 ? 10 : $dimensions["width"],
                "weight" => $dimensions["weight"] = 0 ? 1 : $dimensions["weight"],
                "height" => $dimensions["height"] = 0 ? 10 : $dimensions["height"]
            ];

            $fullItems[] = $temparray;

        }

        return $fullItems;
    }

}
