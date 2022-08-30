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
    'uiRegistry',
    'jquery',
    'ko',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/lib/collapsible',
    'mage/translate'
], function (Component, registry, $, ko, _, utils, Collapsible) {
    'use strict';

    //connect items with observableArrays
    ko.bindingHandlers.sortableList = {
        /** @inheritdoc */
        init: function (element, valueAccessor) {
            var list = valueAccessor();

            $(element).sortable({
                axis: 'y',
                handle: '[data-role="draggable"]',
                tolerance: 'pointer',

                /** @inheritdoc */
                update: function (event, ui) {
                    var item = ko.contextFor(ui.item[0]).$data,
                        position = ko.utils.arrayIndexOf(ui.item.parent().children(), ui.item[0]);

                    if (ko.contextFor(ui.item[0]).$index() != position) { //eslint-disable-line eqeqeq
                        if (position >= 0) {
                            list.remove(item);
                            list.splice(position, 0, item);
                        }
                        ui.item.remove();
                    }
                }
            });
        }
    };

    return Collapsible.extend({
        defaults: {
            notificationMessage: {
                text: null,
                error: null
            },
            modules: {
                variationsComponent: '${ $.variationsComponent }',
            },
            inputPath: '${ $.dropdownProvider }',
            imports: {
                dropdowns: '${ $.dropdownProvider }:elems'
            },
            listens: {
                '${ $.multiselectName }:rows': 'doSelectSavedAttributes'
            },
            createOptionsUrl: null,
            attributes: [],
            dropdownValues: [],
            productsIds: [],
            stepInitialized: false,
            variationsExists: ko.observable(true)
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.createDropdowns = _.wrap(this.createDropdowns, function () {
                var args = _.toArray(arguments),
                    createDropdowns = args.shift();
                return this.doInitSavedOptions(createDropdowns.apply(this, args));
            });
            this.createDropdowns = _.memoize(this.createDropdowns.bind(this), _.property('id'));
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(['attributes', 'dropdownValues', 'productsIds']);

            return this;
        },

        // variationsExists: function () {
        //     console.log(this.variationsComponent().value());
        //     return this.variationsComponent().value().length === 0;
        // },

        doCreatedDropdowns: function (doropdowns) {
            //console.log(doropdowns);
            this.dropdownValues(_.map(doropdowns, this.createDropdowns));
        },

        /**
         * Create option.
         */
        createOption: function () {
            var uniqueid = utils.uniqueid();
            // this - current attribute
            this.options.push({
                value: uniqueid,
                label: '',
                id: uniqueid,
                'attribute_id': this.id,
                newDropdown: this.newDropdown,
                'is_new': true,
                'is_saved': false
            });
        },

        /**
         * @param {Object} option
         */
        saveOption: function (option) {
            var self = this;
            if (!_.isEmpty(option.label)) {
                // this.chosenOptions.push(option.id);
                this.options.remove(option);
                option.is_saved = true;
                this.options.push(option);
                this.filterOptions.push(option);
                var dropdownOptions = 'webkul_partfinder_partfinder_form.webkul_partfinder_partfinder_form.dropdown_options.dropdowns.' +
                    this.index + '.container_option.container_common.options';

                registry.get(dropdownOptions, function (input) {
                    input.value(self.options());
                });
            }
        },

        /**
         * @param {Object} option
         */
        removeOption: function (option) {
            this.options.remove(option);
        },

        /**
         * @param {String} dropdown
         */
        removeDropdownValues: function (dropdown) {
            dropdown.chosenOptions.removeAll();
            dropdown.options.removeAll();
            this.wizard.setNotificationMessage(
                $.mage.__('Dropdown values have been removed.')
            );
        },

        /**
         * @param {Object} attribute
         * @param {*} index
         * @return {Object}
         */
        createDropdowns: function (dropdown, index) {
            dropdown.chosenOptions = ko.observableArray([]);
            dropdown.filterOptions = ko.observableArray(_.map(dropdown.options, function (option) {
                if (!_.isUndefined(option.entity_id)) {
                    option.id = option.entity_id
                } else {
                    option.id = utils.uniqueid();
                }
                option.newDropdown = dropdown.newDropdown;
                return option;
            }));
            dropdown.options = ko.observableArray(_.map(dropdown.options, function (option) {
                if (!_.isUndefined(option.entity_id)) {
                    option.id = option.entity_id
                } else {
                    option.id = utils.uniqueid();
                }
                option.newDropdown = dropdown.newDropdown;
                return option;
            }));

            dropdown.opened = ko.observable(true);
            dropdown.collapsible = ko.observable(true);
            return dropdown;
        },

        /**
         * First 3 attribute panels must be open.
         *
         * @param {Number} index
         * @return {Boolean}
         */
        initialOpened: function (index) {
            return index < 3;
        },

        /**
         * Save attribute.
         */
        saveDropdowns: function () {
            var errorMessage = $.mage.__('Select or Create options for all dropdowns or remove unused options.');

            this.dropdownValues.each(function (dropdown) {
                dropdown.chosen = [];

                if (!dropdown.chosenOptions.getLength()) {
                    throw new Error(errorMessage);
                }
                dropdown.chosenOptions.each(function (id) {
                    dropdown.chosen.push(dropdown.options.findWhere({
                        id: id
                    }));
                });
            });

            if (!this.dropdownValues().length) {
                throw new Error(errorMessage);
            }
        },

        /**
         * @param {Object} attribute
         */
        selectAllAttributes: function (attribute) {
            this.chosenOptions(_.pluck(attribute.options(), 'id'));
        },

        /**
         * @param {Object} attribute
         */
        deSelectAllAttributes: function (attribute) {
            attribute.chosenOptions.removeAll();
        },

        /**
         * @return {Boolean}
         */
        saveOptions: function () {
            var options = [];

            this.dropdownValues.each(function (dropdown) {
                dropdown.chosenOptions.each(function (id) {
                    var option = dropdown.options.findWhere({
                        id: id,
                        'is_new': true
                    });

                    if (option) {
                        options.push(option);
                    }
                });
            });

            if (!options.length) {
                return false;
            }
        },

        /**
         * @param {*} productsIds
         */
        requestAttributes: function (productsIds) {
            this.productsIds(productsIds);
            var dropdownData = [];
            _.each(this.dropdowns, function (uiClass) {
                var elements = uiClass.elems();
                var data = {};
                _.each(elements, function (containerPath) {
                    var path = containerPath.name;
                    data.newDropdown = false;
                    registry.get(path + '.container_common.title', function (input) {
                        data.title = input.value();
                    });
                    registry.get(path + '.container_common.option_id', function (input) {
                        data.id = input.value();
                        if (data.id == "") {
                            data.newDropdown = true;
                        }
                    });
                    registry.get(path + '.container_common.options', function (input) {
                        data.options = input.value();
                    });
                    data.index = uiClass.index;
                    if (data.id == "") {
                        data.id = parseInt(uiClass.index) + 1;
                    }
                    dropdownData.push(data);

                    //
                }, this);

            }, this);

            this.dropdownValues(_.map(dropdownData, this.createDropdowns));
        },

        /**
         * @param {*} attribute
         * @return {*}
         */
        doInitSavedOptions: function (attribute) {

            var selectedOptions, selectedOptionsIds, selectedAttribute = _.findWhere(this.initData.attributes, {
                id: attribute.id
            });

            if (selectedAttribute) {
                selectedOptions = _.pluck(selectedAttribute.chosen, 'value');
                selectedOptionsIds = _.pluck(_.filter(attribute.options(), function (option) {
                    return _.contains(selectedOptions, option.value);
                }), 'id');
                attribute.chosenOptions(selectedOptionsIds);
                this.initData.attributes = _.without(this.initData.attributes, selectedAttribute);
            }

            return attribute;
        },

        /**
         * filter options
         */
        filterDropdownValue: function (value, element) {
            var filteredOptions = [];
            filteredOptions = ko.computed(function () {
                let filter = $(element.currentTarget).val();
                filter = filter.toLowerCase();
                if (!filter || filter == "") {
                    return _.reject(value.options(), function (option) {
                        return (option.is_saved == false);
                    });
                } else {
                    return ko.utils.arrayFilter(_.reject(value.options(), function (option) {
                        return (option.is_saved == false);
                    }), function (i) {
                        return ko.utils.stringStartsWith(i.label.toLowerCase(), filter);
                    });
                }
            });
            value.filterOptions(filteredOptions());
            return true;
        },

        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            if (this.variationsComponent().value().length) {
                this.variationsExists(false);
            }
            this.wizard = wizard;
            this.requestAttributes(wizard.data.productsIds());
        },

        /**
         * @param {Object} wizard
         */
        force: function (wizard) {
            this.saveOptions();
            this.saveDropdowns(wizard);
            wizard.data.dropdowns = this.dropdownValues;
            wizard.data.variations = this.generateVariation(this.dropdownValues());
        },

        /**
         * @param {Object} dropdowns - example [['b1', 'b2'],['a1', 'a2', 'a3'],['c1', 'c2', 'c3'],['d1']]
         * @returns {*} example [['b1','a1','c1','d1'],['b1','a1','c2','d1']...]
         */
        generateVariation: function (dropdowns) {
            return _.reduce(dropdowns, function (matrix, dropdown) {
                var tmp = [];

                _.each(matrix, function (variations) {
                    _.each(dropdown.chosen, function (option) {
                        option['dropdown_id'] = dropdown.id;
                        option['dropdown_label'] = dropdown.title;
                        tmp.push(_.union(variations, [option]));
                    });
                });

                if (!tmp.length) {
                    return _.map(dropdown.chosen, function (option) {
                        option['dropdown_id'] = dropdown.id;
                        option['dropdown_label'] = dropdown.title;

                        return [option];
                    });
                }

                return tmp;
            }, []);
        },

        /**
         * @param {Object} wizard
         */
        back: function (wizard) {
            wizard.data.productsIds(this.productsIds());
        }
    });
});