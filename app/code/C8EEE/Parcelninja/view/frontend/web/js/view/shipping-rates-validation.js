/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        '../model/shipping-rates-validator',
        '../model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        ParcelninjaShippingProviderShippingRatesValidator,
        ParcelninjaShippingProviderShippingRatesValidationRules
    ) {
        "use strict";
        defaultShippingRatesValidator.registerValidator('C8EEE_parcelninja', ParcelninjaShippingProviderShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('C8EEE_parcelninja', ParcelninjaShippingProviderShippingRatesValidationRules);
        return Component;
    }
);
