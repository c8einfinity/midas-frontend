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
    'uiComponent',
    'jquery',
    'underscore',
    'mage/translate'
], function (Component, $, _) {
    'use strict';

    /**
     * @param {Function} provider
     */
    var initNewAttributeListener = function (provider) {
        $('[data-role=finder-variations-matrix]').on('add', function () {
            provider().reload();
        });
    };

    return Component.extend({
        productsLables: {},
        stepInitialized: false,
        defaults: {
            modules: {
                multiselect: '${ $.multiselectName }',
                attributeProvider: '${ $.providerName }'
            },
            listens: {
                '${ $.multiselectName }:selected': 'doSelectedProductLabels',
                '${ $.multiselectName }:rows': 'doSelectSavedAttributes'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            selectedProducts: [],
            products: []
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.selected = [];

            initNewAttributeListener(this.attributeProvider);
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(['selectedProducts', 'products']);

            return this;
        },

        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;
            this.selected = [];
            if (!_.isUndefined(this.multiselect().selected)) {
                this.multiselect().selected([]);
            }
            //this.setNotificationMessage();
        },

        /**
         * Set notification message.
         */
        setNotificationMessage: function () {
            /*eslint-disable max-len*/
            var msg = $.mage.__('When you remove or add an product, we automatically update all configurations and you will need to recreate current configurations manually.');

            /*eslint-enable max-len*/

            if (this.mode === 'edit') {
                this.wizard.setNotificationMessage(msg);
            }
        },

        /**
         * to show the saved values
         * Modify here to set default products
         * Do select saved attributes.
         */
        doSelectSavedAttributes: function () {
            if (this.stepInitialized === false) {
                this.stepInitialized = true;
                //cache attributes labels, which can be present on the 2nd page
                _.each(this.initData.attributes, function (attribute) {
                    this.productsLables[attribute.id] = attribute.label;
                }.bind(this));
                this.multiselect().selected(_.pluck(this.initData.attributes, 'id'));
            }
        },

        /**
         * @param {*} selected
         */
        doSelectedProductLabels: function (selected) {
            var labels = [];

            this.selected = selected;
            _.each(selected, function (productId) {
                var product;

                if (!this.productsLables[productId]) {
                    product = _.findWhere(this.multiselect().rows(), {
                        'entity_id': productId
                    });

                    if (product) {
                        this.productsLables[product['entity_id']] = product['name'];
                    }
                }
                labels.push(this.productsLables[productId]);
                this.products.push(product);
            }.bind(this));
            this.selectedProducts(labels.join(', '));
        },

        /**
         * @param {Object} wizard
         */
        force: function (wizard) {
            wizard.data.productsIds = this.multiselect().selected;
            this.products([]);
            _.each(this.multiselect().selected(), function (productId) {
                var product;
                product = _.findWhere(this.multiselect().rows(), {
                    'entity_id': productId
                });
                this.products.push(product);
            }.bind(this));
            wizard.data.products = this.products();

            if (!wizard.data.productsIds() || wizard.data.productsIds().length === 0) {
                throw new Error($.mage.__('Please select products(s).'));
            }
            this.setNotificationMessage();
        },

        /**
         * Back.
         */
        back: function () {}
    });
});