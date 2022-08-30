<?php

namespace C8EEE\Parcelninja\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;

interface GatewayInterface
{
    public function getQuotes(RateRequest $request);

}
