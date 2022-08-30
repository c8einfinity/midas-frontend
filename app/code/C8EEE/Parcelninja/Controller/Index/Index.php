<?php
/**
 * Copyright � C8EEE Ltd. All rights reserved.
 *
 * @package    C8EEE_Parcelninja
 * @copyright  Copyright � C8EEE Ltd (http://www.C8EEE.com)
 */

namespace C8EEE\Parcelninja\Controller\Index;

use Magento\Framework\App\Action\Context;
use C8EEE\Parcelninja\Gateway\Parcelninja\Request as ParcelninjaRequest;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $ParcelninjaRequest;

    public function __construct(
        ParcelninjaRequest $ParcelninjaRequest,
        array $data = []
    ){
        $this->ParcelninjaRequest = $ParcelninjaRequest;
    }

    public function execute()
    {
        $quotes = $this->ParcelninjaRequest->sendRequest(
            \C8EEE\Parcelninja\Gateway\Parcelninja\Endpoints\GetQuotes::class
            ,
            [
                'deliveryInformation' => '',
                'items'               => ''
            ]
        );
        return json_encode($quotes);
    }

}
