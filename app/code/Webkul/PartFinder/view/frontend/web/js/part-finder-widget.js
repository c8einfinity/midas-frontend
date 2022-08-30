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
    'jquery',
    'uiComponent',
    'mageUtils',
    'ko',
    'underscore'
], function ($, Component, utils, ko, _) {
    'use strict';

    return Component.extend({
        defaults: {
            dropdowns: [],
            finders: [],
            columns: ko.observableArray([]),
            selected: [],
            selection: '',
            newOptionValue: ko.observableArray([]),
            variations: [],
            params: '',
            totalDropdowns: ko.observable(0),
            form: '',
            url: '',
            locationUrl: ''
        },

        initialize: function () {
            var self = this;
            this._super();
            this.intiOptionsObservable();
            // update url param as per option selection
            this.selected.subscribe(function (value) {
                self.selection(
                    _.reject(
                        _.pluck(
                            _.sortBy(self.selected(), 'dropdown'),
                            'selected'
                        ),
                        _.isUndefined
                    ).join('-')
                );
            });

            // change form url for category finder
            self.selection.subscribe(function (value) {
                self.changeUrl()
            });
            this.setSelection();
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Component} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('dropdowns finders selected selection variations params locationUrl');

            return this;
        },

        /**
         * create each dropdown optins as observable
         */
        intiOptionsObservable: function () {
            var self = this;
            _.each(this.dropdowns(), function (dropdown, index) {
                dropdown.filterOptions = ko.observableArray(dropdown.options);
                dropdown.options = ko.observableArray(dropdown.options);
                dropdown.selected = ko.observable();
                dropdown.uid = utils.uniqueid();
                dropdown.selected.subscribe(function (value) {
                    if (!_.contains(_.pluck(self.selected(), 'dropdown'), dropdown.dropdown_id)) {
                        self.selected.push({
                            'dropdown': dropdown.dropdown_id,
                            'selected': value
                        });
                    } else {
                        var temp = _.reject(self.selected(), function (data) {
                            return data.dropdown == dropdown.dropdown_id;
                        });
                        self.selected(temp);
                        if (value != '') {
                            self.selected.push({
                                'dropdown': dropdown.dropdown_id,
                                'selected': value
                            });
                        }
                    }
                    if (!_.isUndefined(self.dropdowns()[index + 1])) {
                        self.filterNextDropdown(self.dropdowns()[index + 1], value);
                    }
                });
            });
        },

        /**
         * change next dropdown on previous change
         */
        filterNextDropdown: function (dropdown, parent) {
            var self = this;
            var filterOptions = ko.computed(function () {
                return _.map(dropdown.options(), function (value) {
                    if (_.contains(self.variations()[parent], value.option_id)) {
                        return value;
                    }
                });
            });
            dropdown.filterOptions(_.reject(filterOptions(), _.isUndefined));
        },

        /**
         * set option selection
         */
        setSelection: function () {
            var self = this;
            _.each(this.dropdowns(), function (dropdown, index) {
                _.each(self.params().split('-'), function (paramValue) {
                    if (_.contains(_.pluck(dropdown.filterOptions(), 'option_id'), paramValue)) {
                        dropdown.selected(paramValue);
                    }
                });
            });
        },

        /**
         * clear category finder and reload current page
         */
        clearFinder: function (model, event) {
            event.preventDefault();
            _.each(this.dropdowns(), function (dropdown, index) {
                dropdown.selected('');
            });
            this.selected([]);
            this.selection('');
            location.href = this.locationUrl();
        },

        /**
         * submit category finder
         */
        submitFinder: function (model, event) {
            event.preventDefault();
            if ($(this.form).valid()) {
                location.href = this.locationUrl();
                return false;
            }

        },

        /**
         * clear non category or any global part finder
         */
        clearGlobalFinder: function (model, event) {
            event.preventDefault();
            _.each(this.dropdowns(), function (dropdown, index) {
                dropdown.selected('');
            });
            this.selected([]);
            this.selection('');
        },

        /**
         * submit global part finder and redirect to search result page
         */
        submitGlobalFinder: function () {
            event.preventDefault();
            if ($(this.form).valid()) {
                $(this.form).find('.finder-dropdown').attr("disabled", "disabled");
                $(this.form).submit();
                return false;
            }
        },

        /**
         * @param {String} paramName
         * @param {*} paramValue
         * @param {*} defaultValue
         */
        changeUrl: function () {
            var decode = window.decodeURIComponent,
                urlPaths = this.url.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters, i;

            for (i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined ?
                    decode(parameters[1].replace(/\+/g, '%20')) :
                    '';
            }

            if (this.selection() != '') {
                paramData['finder'] = this.selection();
            } else {
                delete paramData['finder'];
            }
            paramData = $.param(paramData);
            this.locationUrl(baseUrl + (paramData.length ? '?' + paramData : ''));
        }
    });
});