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
        'jquery',
        'Magento_Ui/js/form/form',
        './adapter',
        'uiRegistry',
        'Magento_Ui/js/lib/spinner',
        'rjsResolver',
        'mage/validation'
    ],
    function ($, Form, adapter, registry, loader, resolver) {
        'use strict';

        /**
         * Collect form data.
         *
         * @param {Array} items
         * @returns {Object}
         */
        function collectData(items)
        {
            var result = {},
                name;

            items = Array.prototype.slice.call(items);
            items.forEach(function (item) {
                switch (item.type) {
                    case 'checkbox':
                        result[item.name] = +!!item.checked;
                        break;

                    case 'radio':
                        if (item.checked) {
                            result[item.name] = item.value;
                        }
                        break;

                    case 'select-multiple':
                        name = item.name.substring(0, item.name.length - 2); //remove [] from the name ending
                        result[name] = _.pluck(item.selectedOptions, 'value');
                        break;
                    default:
                        result[item.name] = item.value;
                }
            });

            return result;
        }

        /**
         * Check if fields is valid.
         *
         * @param {Array}items
         * @returns {Boolean}
         */
        function isValidFields(items)
        {
            var result = true;

            _.each(items, function (item) {
                if (!$.validator.validateSingleElement(item)) {
                    result = false;
                }
            });

            return result;
        }


        return Form.extend({
            defaults: {
                nameModal: '',
            },
            initAdapter: function () {
                adapter.on({
                    'reset': this.reset.bind(this),
                    'save': this.save.bind(this, true, {}),
                    'saveAndContinue': this.save.bind(this, false, {}),
                    'startImport': this.startImport.bind(this, {})
                }, this.selectorPrefix, this.eventPrefix);

                return this;
            },

            /**
             * Destroy adapter handlers.
             *
             * @returns {Object}
             */
            destroyAdapter: function () {
                adapter.off([
                    'reset',
                    'save',
                    'saveAndContinue',
                    'startImport'
                ], this.eventPrefix);

                return this;
            },

            startImport: function (data) {
                this.validate();
                if (!this.additionalInvalid && !this.source.get('params.invalid')) {
                    this.setAdditionalData(data)
                        .submitImport(true);
                } else {
                    this.focusInvalid();
                }
            },

            /**
             * Submits form
             *
             * @param {String} redirect
             */
            submitImport: function (redirect) {
                var additional = collectData(this.additionalFields),
                    source = this.source;

                _.each(additional, function (value, name) {
                    source.set('data.' + name, value);
                });
                registry.get(this.namespace + "." + this.namespace + "." + this.runModal).toggleModal();
            },

            validate: function () {
                this.additionalFields = document.querySelectorAll(this.selector);
                this.source.set('params.invalid', false);
                this.source.trigger('data.validate');
                this.set('additionalInvalid', !isValidFields(this.additionalFields));
            },
        });
    }
)