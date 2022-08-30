/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_PartFinder
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
        'underscore',
        'Magento_Ui/js/form/element/abstract'
    ],
    function (_, Acstract) {
        'use strict';

        return Acstract.extend(
            {
                defaults: {
                    percent: 0,
                    percentWidth: '0%',
                    showConsole: true
                },
                initObservable: function () {
                    this._super()
                        .observe('percent percentWidth showDebug');
                    return this;
                },
            }
        );
    }
);
