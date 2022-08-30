<?php

namespace C8EEE\Parcelninja\Plugin;

class ShippingMethodConverter
{

	public function afterModelToDataObject(\Magento\Quote\Model\Cart\ShippingMethodConverter $subject, $result, $rateModel)
	{
		$extensionAttributes = $result->getExtensionAttributes();

		$extensionAttributes->setPnCost($rateModel->getData('pn_cost'));

		$extensionAttributes->setPnQuoteId($rateModel->getData('pn_quote_id'));

		$result->setExtensionAttributes($extensionAttributes);

		return $result->setErrorMessage($result->getErrorMessage());

	}

}
