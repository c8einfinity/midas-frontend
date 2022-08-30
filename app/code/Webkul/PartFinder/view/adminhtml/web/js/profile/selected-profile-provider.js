/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_PartFinder
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'underscore'
    ],
    function (ko, _) {
        return {
            selectedProfile: ko.observable(),
            getProfile: function () {
                return this.selectedProfile();
            }
        };
    }
);
