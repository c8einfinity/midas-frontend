/**
 * Motus Quotesystem
 */
/*jshint jquery:true*/
define(
    [
    "jquery",
    ], function ($) {
        'use strict';
        $.widget(
            'mage.motCompareList', {
                _create: function () {
                    var self = this;
                    var showCart = self.options.showCart;
                    if (!showCart) {
                        $('td.cell.product.info').each(
                            function () {
                                if ($(this).find('.quote_button').length > 0) {
                                    $(this).find('.action.tocart.primary').remove();
                                }
                            }
                        );
                    }
                },
            }
        );
        return $.mage.motCompareList;
    }
);