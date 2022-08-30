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
    './selected-profile-provider',
    'mage/translate'
], function (Component, $, ko, _, profileProvider) {
    'use strict';

    /**
     * @param {Function} provider
     */
    var initNewProfileListener = function (provider, mappingData) {
        $('[data-role=finder-variations-matrix]').on('add', function (data, value) {
            mappingData(value);
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
            mappingData: [],
            profiles: [],
            dropdowns: ko.observableArray([]),
            selected: null,
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            var self = this;
            self.dropdowns.push(self.dropdownsData);
            initNewProfileListener(this.attributeProvider, this.mappingData);
            this.mappingData.subscribe(function (newData) {
                if (newData.id) {
                    var exists = _.find(self.profiles(), function (profile) {
                        return profile.id == newData.id;
                    });

                    if (!exists) {
                        self.profiles.push({ 'id': newData.id, 'label': newData.label });
                        self.selected(newData.id);
                    }
                }
                if (newData.dropdowns.length) {
                    var data = {};
                    data[newData.id] = newData.dropdowns;
                    self.dropdowns.push(data);
                }
            });
            this.selected.subscribe(function (newData) {
                profileProvider.selectedProfile(newData);
            });
        },

        profileDropdowns: function () {
            var self = this;
            if (!self.selected) {
                return [];
            }
            var filterData = [];
            this.dropdowns().filter(function (f, i) {
                var isok = false;
                _.each(f, function (value, index) {
                    if (index == self.selected()) {
                        isok = true;
                    }
                });
                if (isok) {
                    filterData = f[self.selected()];
                }
            });
            return filterData;
        },

        profileDropdownsJson: function () {
            return JSON.stringify(this.profileDropdowns());
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(['mappingData', 'profiles', 'selected']);

            return this;
        }
    });
});
