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
    'uiRegistry',
    'Magento_Ui/js/grid/paging/paging',
    'mage/translate'
], function (Component, $, ko, _, registry, paging) {
    'use strict';

    return Component.extend({
        defaults: {
            modules: {
                variationsComponent: '${ $.variationsComponent }',
                modalComponent: '${ $.modalComponent }',
                profileModalComponent: '${ $.profileModalComponent }'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            gridExisting: [],
            gridNew: [],
            gridNewTemp: [],
            dropdownTemp: [],
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
            gridTemplate: 'Webkul_PartFinder/variations/summary-grid'
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

            this._super().observe('gridExisting gridNew gridDeleted dropdowns attributes sections, dropdownTemp');
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
                gridDeleted = [];

            // this.gridNewTemp = []
            // this.variations = [];
            _.each(variations, function (options) {
                _.each(products, function (product) {
                    if (_.isUndefined(product)) {
                        return;
                    }
                    var images, sku, name, quantity, price, variation,
                        productId;

                    productId = product['entity_id'];
                    images = {
                        preview: product['thumbnail_image']
                    };
                    sku = product.sku;
                    name = product.name;
                    quantity = product.qty;
                    status = product.status == 1 ? $.mage.__('Enabled') : $.mage.__('Disabled');
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
                    this.gridNewTemp.push(this.prepareRowForGrid(variation));
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
            this.variationsNew = this.gridNewTemp;
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
            _.each(attributes, function (attribute, index) {
                columns.splice(3 + index, 0, attribute.dropdown_label);
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
            this.dropdownTemp.push(this.wizard.data.dropdowns);
            this.dropdowns(this.wizard.data.dropdowns);
            this.attributes(this.wizard.data.dropdowns);
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
            var tempOptions = [];
            var finderDropdonws = registry.get('webkul_partfinder_partfinder_form.webkul_partfinder_partfinder_form.dropdown_options.dropdowns');
            var recordData = finderDropdonws.recordData();
            _.each(this.dropdownTemp(), function (dropdowns) {
                var options = _.pluck(dropdowns, 'options');
                _.each(options, function (option) {
                    _.each(option, function (value) {
                        value.value = value.id;
                        if (_.isUndefined(tempOptions[value.dropdown_id])) {
                            tempOptions[value.dropdown_id] = [value];
                        } else {
                            var savedValues = tempOptions[value.dropdown_id];
                            var isValue = false;
                            _.each(savedValues, function (savedValue, callback) {
                                if (savedValue.label == value.label) {
                                    isValue = true;
                                }
                            });
                            if (isValue === false) {
                                tempOptions[value.dropdown_id].push(value);
                            }
                        }
                    });
                });
            });
            var sortOrder = 0;
            var recordId = 0;
            _.each(recordData, function (value) {
                sortOrder = value.sort_order;
                recordId = value.record_id;
            });
            var count = sortOrder;
            var recordCount = recordId;
            _.each(this.dropdowns(), function (dropdowns) {
                _.omit(dropdowns, 'chosenOptions');
                _.omit(dropdowns, 'chosen');
                dropdowns.options = tempOptions[dropdowns.dropdown_id];
                dropdowns.title = dropdowns.dropdown_label;
                dropdowns.sort_order = parseInt(count) + 1;
                dropdowns.record_id = parseInt(count) + 1;
                dropdowns.option_id = '';
                dropdowns.is_require = "0";
                dropdowns.index = parseInt(count) > 0 ? parseInt(count) - 1 : parseInt(count);
                var found = false;
                _.each(finderDropdonws.relatedData, function(savedDropdown, index){
                    if (savedDropdown.title == dropdowns.title) {
                        if (!_.isUndefined(savedDropdown.options[0])) {
                            var optionValue = savedDropdown.options[0].value;
                            savedDropdown.chosenOptions = [];
                            savedDropdown.chosenOptions.push(optionValue);
                        }
                        found = true;
                        return false;
                    }
                });
                if (found) {
                    return;
                }
                finderDropdonws.relatedData.push(dropdowns);
                finderDropdonws.recordData.push(dropdowns);
                count++;
                recordCount++;
            });
            finderDropdonws.setToInsertData();
            finderDropdonws.initChildren();

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
            this.profileModalComponent().closeModal();
        },

        /**
         * Back.
         */
        back: function () {}
    });
});
