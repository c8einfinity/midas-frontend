/**
 * Quotes_edit.xml
 */

require(
    [
    'jquery'
    ], function ($) {
        'use strict';
        $(document).ajaxStop(
            function () {
                if (!parseInt($('select[name="product[quote_status]"] option:selected').val())) {
                    $('input[name="product[min_quote_qty]"]').prop('disabled', true);
                }
                $('select[name="product[quote_status]"]').change(
                    function () {
                        if (!parseInt($('select[name="product[quote_status]"] option:selected').val())) {
                            $('input[name="product[min_quote_qty]"]').prop('disabled', true);
                        } else {
                            $('input[name="product[min_quote_qty]"]').prop('disabled', false);
                        }
                    }
                )
            }
        );
    }
);