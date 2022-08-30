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
    'ko',
    'underscore',
    'Magento_Ui/js/grid/paging/paging',
    'mage/translate'
], function (Component, $, ko, _, paging) {
    'use strict';

    return Component.extend({
        defaults: {
            modules: {
                variationsComponent: '${ $.variationsComponent }',
                modalComponent: '${ $.modalComponent }'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            gridExisting: [],
            gridNew: [],
            gridDeleted: [],
            variationsExisting: [],
            variationsNew: [],
            variationsDeleted: [],
            pagingExisting: paging({
                name: 'partfinderWizard.pagingExisting',
                sizesConfig: {
                    component: 'Magento_ConfigurableProduct/js/variations/paging/sizes',
                    name: 'partfinderWizard.pagingExisting_sizes'
                }
            }),
            pagingNew: paging({
                name: 'partfinderWizard.pagingNew',
                sizesConfig: {
                    component: 'Magento_ConfigurableProduct/js/variations/paging/sizes',
                    name: 'partfinderWizard.pagingNew_sizes'
                }
            }),
            pagingDeleted: paging({
                name: 'partfinderWizard.pagingDeleted',
                sizesConfig: {
                    component: 'Magento_ConfigurableProduct/js/variations/paging/sizes',
                    name: 'partfinderWizard.pagingDeleted_sizes'
                }
            }),
            dropdowns: [],
            attributes: [],
            attributesName: [$.mage.__('Images'), $.mage.__('SKU'), $.mage.__('Quantity'), $.mage.__('Status')],
            //sections: [],
            gridTemplate: 'Magento_ConfigurableProduct/variations/steps/summary-grid'
        },

        /** @inheritdoc */
        initObservable: function () {
            var pagingObservables = {
                currentNew: ko.getObservable(this.pagingNew, 'current'),
                currentExisting: ko.getObservable(this.pagingExisting, 'current'),
                currentDeleted: ko.getObservable(this.pagingDeleted, 'current'),
                pageSizeNew: ko.getObservable(this.pagingNew, 'pageSize'),
                pageSizeExisting: ko.getObservable(this.pagingExisting, 'pageSize'),
                pageSizeDeleted: ko.getObservable(this.pagingDeleted, 'pageSize')
            };

            this._super().observe('gridExisting gridNew gridDeleted dropdowns attributes sections');
            this.gridExisting.columns = ko.observableArray();
            this.gridNew.columns = ko.observableArray();
            this.gridDeleted.columns = ko.observableArray();

            _.each(pagingObservables, function (observable) {
                observable.subscribe(function () {
                    this.generateGrid();
                }, this);
            }, this);

            return this;
        },
        nextLabelText: $.mage.__('Generate Products'),
        variations: [],

        /**
         * @param {*} variations
         * @param {Function} getSectionValue
         */
        calculate: function (variations, products) {
            var productSku = this.variationsComponent().getProductValue('sku'),
                productPrice = this.variationsComponent().getProductPrice(),
                productWeight = this.variationsComponent().getProductValue('weight'),
                productName = this.variationsComponent().getProductValue('name'),
                variationsKeys = [],
                gridExisting = [],
                gridNew = [],
                gridDeleted = [];

            this.variations = [];
            _.each(variations, function (options) {
                _.each(products, function (product) {
                    if (_.isUndefined(product)) {
                        return;
                    }
                    var images, sku, name, quantity, price, variation,
                        productId;

                    productId = product['entity_id'];
                    images = {
                        preview: product['thumbnail_src']
                    };
                    sku = product.sku;
                    name = product.name;
                    quantity = product.qty;
                    status = product.status;
                    variation = {
                        options: options,
                        images: images,
                        sku: sku,
                        name: name,
                        quantity: quantity,
                        status: status,
                        productId: productId,
                        editable: true
                    };
                    gridNew.push(this.prepareRowForGrid(variation));
                    this.variations.push(variation);
                }, this);
            }, this);

            _.each(_.omit(this.variationsComponent().productAttributesMap, variationsKeys), function (productId) {
                gridDeleted.push(this.prepareRowForGrid(
                    _.findWhere(this.variationsComponent().variations, {
                        productId: productId
                    })
                ));
            }.bind(this));

            this.variationsExisting = gridExisting;
            this.variationsNew = gridNew;
            this.variationsDeleted = gridDeleted;

        },

        /**
         * Generate grid.
         */
        generateGrid: function () {
            var pageExisting = this.pagingExisting.pageSize * this.pagingExisting.current,
                pageNew = this.pagingNew.pageSize * this.pagingNew.current,
                pageDeleted = this.pagingDeleted.pageSize * this.pagingDeleted.current;

            this.pagingExisting.totalRecords = this.variationsExisting.length;
            this.gridExisting(this.variationsExisting.slice(pageExisting - this.pagingExisting.pageSize, pageExisting));

            this.pagingNew.totalRecords = this.variationsNew.length;
            this.gridNew(this.variationsNew.slice(pageNew - this.pagingNew.pageSize, pageNew));

            this.pagingDeleted.totalRecords = this.variationsDeleted.length;
            this.gridDeleted(this.variationsDeleted.slice(pageDeleted - this.pagingDeleted.pageSize, pageDeleted));
        },

        /**
         * @param {Object} variation
         * @return {Array}
         */
        prepareRowForGrid: function (variation) {
            var row = [];

            row.push(_.extend({
                images: []
            }, variation.images));
            row.push(variation.sku);
            row.push(variation.quantity);
            _.each(variation.options, function (option) {
                row.push(option.label);
            });
            row.push(variation.status);

            return row;
        },

        /**
         * @return {String|*}
         */
        getGridTemplate: function () {
            return this.gridTemplate;
        },

        /**
         * @return {*|String}
         */
        getGridId: function () {
            return _.uniqueId('grid_');
        },

        /**
         * @param {*} attributes
         * @return {Array}
         */
        getColumnsName: function (attributes) {
            var columns = this.attributesName.slice(0);

            attributes.each(function (attribute, index) {
                columns.splice(3 + index, 0, attribute.title);
            }, this);

            return columns;
        },

        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;
            this.wizard.nextLabel = this.nextLabelText;
            //this.sections(wizard.data.sections());
            this.dropdowns(wizard.data.dropdowns());
            this.attributes(wizard.data.dropdowns());
            this.gridNew([]);
            this.gridExisting([]);
            this.gridDeleted([]);
            this.gridExisting.columns(this.getColumnsName(this.wizard.data.dropdowns));
            this.gridNew.columns(this.getColumnsName(this.wizard.data.dropdowns));
            //this.gridDeleted.columns(this.getColumnsName(this.variationsComponent().productAttributes));
            this.calculate(wizard.data.variations, wizard.data.products);
            this.generateGrid();
        },



        /**
         * Force.
         */
        force: function () {
            var temp = [];
            if (this.variationsComponent().value().length) {
                _.each(this.variationsComponent().value(), function (value) {
                    if (_.contains(_.pluck(this.variations, 'productId'), value.productId) === true) {
                        _.each(_.pluck(this.variations, 'options'), function (option) {
                            _.each(value.options, function (savedOption) {
                                if (option.id !== value.options.id) {
                                    temp.push(value);
                                }
                            });

                        });
                    } else {
                        temp.push(value);
                    }

                }, this);
                _.each(temp, function (value) {
                    this.variations.push(value);
                }, this);
            } else {
                temp = this.variations;
                this.variations = temp;
            }
            _.sortBy(this.variations, 'productId');
            this.variationsComponent().render(this.variations, this.dropdowns());
            this.modalComponent().closeModal();
        },

        /**
         * Back.
         */
        back: function () {}
    });
});