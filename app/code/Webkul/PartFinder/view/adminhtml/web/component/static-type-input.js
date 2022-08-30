/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_PartFinder
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract',
    'mage/translate'
], function (registry, Abstract, $tr) {
    'use strict';

    return Abstract.extend({
        defaults: {
            parentOption: null
        },

        /**
         * Initialize component.
         *
         * @returns {Element}
         */
        initialize: function () {
            return this
                ._super()
                .initLinkToParent();
        },

        /**
         * Cache link to parent component - option holder.
         *
         * @returns {Element}
         */
        initLinkToParent: function () {
            var pathToParent = this.parentName.replace(/(\.[^.]*){2}$/, '');
            this.parentOption = registry.async(pathToParent);
            if (this.parentOption().index == 0) {
                this.label = $tr('Product SKU');
                this.disabled(true);
            } else {
                this.label = $tr('Dropdown #') + this.parentOption().index;
            }
            this.value() && this.parentOption('label', this.value());

            return this;
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            this.parentOption(function (component) {
                component.set('label', value ? value : component.get('headerLabel'));
            });

            return this._super();
        }
    });
});
