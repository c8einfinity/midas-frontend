<?php

namespace C8EEE\Parcelninja\Plugin;

class Rate
{

	public function afterImportShippingRate(\Magento\Quote\Model\Quote\Address\Rate $subject, $result, $rate)
	{
		if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
            return $result;
        } elseif ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            $result->setPnCost(
                $rate->getPnCost()
            )->setPnQuoteId(
                $rate->getPnQuoteId()
            );
        }
        return $result;

	}

}
