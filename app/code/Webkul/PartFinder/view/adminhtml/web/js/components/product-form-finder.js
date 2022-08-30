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
    'Magento_Ui/js/modal/alert',
    'uiRegistry',
    'mageUtils',
    'mage/translate',
    'mage/apply/main',
    'mage/validation'
], function (Component, $, ko, _, alert, registry, utils, $t, main) {
    'use strict';

    return Component.extend({
        defaults: {
            dropdowns: [],
            finders: [],
            columns: ko.observableArray([]),
            selected: '',
            newOptionValue: ko.observableArray([]),
            variations: [],
            totalDropdowns: ko.observable(0),
            displayNotice: false
        },
        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();
            var self = this;

            $.validator.addMethod(
                'validate-all-selected',
                function (value, element) {
                    let isAnySelected = false,
                        totalSelected = 0,
                        tempColumn = [],
                        total = self.columns().length;
                    _.each(self.columns(), function (data) {
                        if (data.chosenOptions().length) {
                            isAnySelected = true;
                            totalSelected++;
                        }
                    });
                    if (total !== totalSelected) {
                        return false;
                    }
                    return true;

                },
                $.mage.__('Please select atleat one option from each dropdown or remove all selection.')
            );
            this.intiOptionsObservable()
            this.initSubscriber();
            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Component} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('dropdowns finders selected variations displayNotice');

            return this;
        },
        /**
         * create each dropdown optins as observable
         */
        intiOptionsObservable: function () {
            var self = this;
            _.each(this.dropdowns(), function (dropdowns) {
                _.each(dropdowns, function (dropdown) {
;
                    dropdown.filterOptions = ko.observableArray(dropdown.options);
                    dropdown.options = ko.observableArray(dropdown.options);
                    dropdown.chosenOptions = ko.observableArray([]);
                    _.each(dropdown.choosen, function (choose) {
                        dropdown.chosenOptions.push(choose);
                    });
                    dropdown.uid = utils.uniqueid();
                    dropdown.chosenOptions.subscribe(function (value) {
                        self.variations(self.generateVariation(self.columns()));
                    });
                });
            });
        },

        /**
         * @param {Object} dropdowns - example [['b1', 'b2'],['a1', 'a2', 'a3'],['c1', 'c2', 'c3'],['d1']]
         * @returns {*} example [['b1','a1','c1','d1'],['b1','a1','c2','d1']...]
         */
        generateVariation: function (dropdowns) {
            return _.reduce(dropdowns, function (matrix, dropdown) {
                var tmp = [];

                _.each(matrix, function (variations) {
                    _.each(dropdown.chosenOptions(), function (option) {
                        tmp.push(_.union(variations, [option]));
                    });
                });

                if (!tmp.length) {
                    return _.map(dropdown.chosenOptions(), function (option) {
                        return [option];
                    });
                }

                return tmp;
            }, []);
        },

        /**
         * on select part finder
         */
        initSubscriber: function () {
            let self = this;
            this.selected.subscribe(function (value) {
                if (!_.isUndefined(value)) {
                    self.columns(_.flatten(_.filter(self.dropdowns(), function (dropdown, index) {
                        return parseInt(index) === parseInt(value);
                    })));
                } else {
                    self.columns([]);
                }
                if (self.columns().length > 0 && value != '') {
                    self.displayNotice(false);
                } else if (!_.isUndefined(value) && self.columns().length == 0) {
                    self.displayNotice(true);
                } else {
                    self.displayNotice(false);
                }
                console.log(self.displayNotice);
                main.apply();
                self.totalDropdowns(self.columns().length);
                self.variations(self.generateVariation(self.columns()));
            });
        },

        /**
         * display dropdown grid
         * If this.columns.length > 0
         */
        displayDropdownGrid: function () {
            return this.columns().length > 0;
        },

        afterRenderfilter: function (element) {
            $(element).trigger('keyup');
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
                    return value.options();
                } else {
                    return ko.utils.arrayFilter(value.options(), function (i) {
                        return ko.utils.stringStartsWith(i.label.toLowerCase(), filter);
                    });
                }
            });
            value.filterOptions(filteredOptions());
            return true;
        },
        addNewOptionValue: function (value, element) {
            var newOptions = {},
                isNewValue = true,
                self = this;
            newOptions.dropdown_id = value.dropdown_id;
            newOptions.value = $(element.currentTarget).val().trim();
            _.each(this.newOptionValue(), function (option, index) {
                if (option.dropdown_id == value.dropdown_id) {
                    option.value = $(element.currentTarget).val().trim();
                    isNewValue = false;
                }
            });
            this.newOptionValue.remove(function (item) {
                return item.value == '';
            });
            if (!this.newOptionValue().length || (isNewValue && newOptions.value != '')) {
                this.newOptionValue().push(newOptions);
            }
        },
        addNew: function (value) {
            _.each(this.newOptionValue(), function (option) {
                if (option.dropdown_id == value.dropdown_id) {
                    var uniqueid = utils.uniqueid();
                    value.filterOptions.push({
                        value: uniqueid,
                        label: option.value,
                        id: uniqueid,
                        option_id: uniqueid,
                        'is_new': true
                    });
                }
            });
        }

    });
});