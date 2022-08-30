<?php

namespace C8EEE\Parcelninja\Plugin;

class ShippingInformationManagement
{

	protected $quoteRepository;

	public function __construct(\Magento\Quote\Api\CartRepositoryInterface $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

	public function afterSaveAddressInformation(\Magento\Checkout\Model\ShippingInformationManagement $subject, $result, $cartId, $addressInformation)
	{
		$quote = $this->quoteRepository->get($cartId);

		$quote->setPnCost($addressInformation->getExtensionAttributes()->getPnCost());
        $quote->setPnQuoteId($addressInformation->getExtensionAttributes()->getPnQuoteId());

        $quote->save();

		return $result;

	}

}
