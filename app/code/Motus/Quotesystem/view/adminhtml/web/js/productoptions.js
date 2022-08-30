/**
 * Motus Quotesystem
 */
define(
    [
    "jquery",
    "Magento_Ui/js/modal/modal",
    'mage/validation',
    "jquery/file-uploader"
    ], function ($,modal) {
        'use strict';
        var customerId;
        $.widget(
            'mage.MotProductoption', {
                options: {

                },
                _create: function () {
                    console.log("we are fired")
                    console.log(self.options.data);
                }
            }  
        )
        return $.mage.MotProductoption;
    }
);