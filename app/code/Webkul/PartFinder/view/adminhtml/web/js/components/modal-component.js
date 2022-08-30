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
    'underscore',
    'Magento_Ui/js/modal/modal-component',
    'mage/storage',
    'uiRegistry',
    'mageUtils',
    'mage/translate'
], function ($, _, Parent, storage, registry, utils, $t) {
    'use strict';

    return Parent.extend({
        defaults: {
            url: '',
            validateUrl: '',
            urlProcess: '',
            currentAjax: '',
            job: 0,
            loading: false,
            template: 'Webkul_PartFinder/components/modal-component',
            editUrl: '',
            isJob: 0,
            end: 0,
            isNotice: true,
            isError: false,
            href: '',
            isHref: false,
            counter: 0,
            notice: $t('Products imported successfully - please click Run button for launch'),
            error: $t('Error')
        },

        /**
         * Wrap content in a modal of certain type
         *
         * @param {HTMLElement} element
         * @returns {Object} Chainable.
         */
        initModal: function (element) {
            this._super();
            $('.save-import').hide();
        },
        actionRun: function () {
            this.isNotice(false);
            this.isError(false);
            $("#console .console").html('');
            $('.save-import').hide();
            var profileId = registry.get(this.job).data.profile_id;
            if (profileId == '') {
                profileId = localStorage.getItem('profileId');
                this.isJob = 1;
            }
            var validateUrl = this.validateUrl + '?id=' + profileId + '&form_key=' + window.FORM_KEY;
            var ajaxImport = this.ajaxImport.bind(this);
            this.validateData(validateUrl).then(ajaxImport);
        },
        initObservable: function () {
            this._super()
                .observe('loading isNotice notice isHref href error isError');
            return this;
        },
        ajaxImport: function (data) {
            this.end = 0;
            this.counter = 0;
            var profileId = registry.get(this.job).data.profile_id;
            var object = registry.get(this.name + '.progress_bar.progress');
            var summary = registry.get(this.name + '.generated_products.products');
            summary.gridNewTemp = [];
            if (data.error == true) {
                $('#console .console').append('<span text="item" class="text-danger">' + data.message + '</span><br/>');
                $(".console").scrollTop($(".console")[0].scrollHeight);
                $('.run-import').text($t('Retry'));
                return true;
            }

            object.percent(0);
            object.percentWidth('0%');
            var url = this.url + '?form_key=' + window.FORM_KEY;
            var self = this,
                data = registry.get(this.job).data;;
            this.loading(true);
            storage.post(
                url,
                JSON.stringify({
                    id: profileId,
                    data: data
                })
            ).done(function (response) {
                if (response.result != false) {
                    object.value(true);
                    if (response.result > 0) {
                        var urls = [];
                        $('#console .console').append('<span text="item" class="text-success">' +
                            response.result + $t(' Rows found to import') +
                            '</span><br/>');
                        $('#console .console').append('<span text="item" class="text">' +
                            $t('Importing.......') +
                            '</span><br/>');
                        object.percent(10);
                        object.percentWidth('10%');
                        var step = Math.round((80 / response.result) * 100) / 100;
                        var finish = false;
                        if (response.result > 0) {
                            finish = self.startProcess(response.result, 0, 0, step, object, []);
                        }
                    } else {
                        self.finish(true);
                        return true;
                    }
                    self.isError(false);
                } else {
                    object.value(false);
                    self.finish(false);
                }
            }).fail(
                function (response) {
                    self.finish(false);
                    self.error(response.responseText);
                }
            );
        },
        validateData: function (validateUrl) {
            var object = $.Deferred(),
                data = registry.get(this.job).data;
            var file = '';
            storage.post(
                validateUrl,
                JSON.stringify(data)
            ).done(
                function (response) {
                    file = response;
                    object.resolve(file);
                }
            ).fail(
                function (response) {
                    file = null;
                    object.resolve(file);
                }
            );
            return object.promise();
        },
        startProcess: function (counter, count, error, step, object, dropdowns) {
            var self = this;
            var tempdropdowns = JSON.stringify({
                dropdowns
            });
            var urlData = {};
            urlData.number = count;
            urlData.dropdown = tempdropdowns;

            //custom code starts
            var finderDropdonws = registry.get('webkul_partfinder_partfinder_form.webkul_partfinder_partfinder_form.dropdown_options.dropdowns');

            urlData.oldData = finderDropdonws.relatedData;
            //custom code ends 

            if (count <= counter - 1) {
                $.post(
                    self.urlProcess + '?form_key=' + window.FORM_KEY,
                    urlData
                ).done(
                    function (response) {
                        if (response.result == true) {
                            $('#console .console').append('<span text="item" class="text-success">' +
                                $t('Generating product variation for row ') + (count + 1) +
                                '</span><br/>');
                            var wizard = {};
                            wizard.data = {};
                            var tempDropdown = self.saveDropdowns(response.dropdowns);
                            dropdowns.push(tempDropdown);
                            //var newDropdown = self.saveDropdowns(response.dropdowns);
                            var variations = self.generateVariation(tempDropdown);
                            wizard.data.dropdowns = tempDropdown;
                            wizard.data.variations = variations;
                            wizard.data.products = response.products;
                            var summary = registry.get(self.name + '.generated_products.products');
                            summary.render(wizard);
                            $('#console .console').append('<span text="item" class="">' +
                                $t('Product variation generated') +
                                '</span><br/>');
                            self.startProcess(counter, count + 1, parseInt(response.count), step, object, dropdowns);
                        } else {
                            $('#console .console').append('<span text="item" class="text-danger">' +
                                response.message +
                                '</span><br/>');
                            //self.finish(false);
                            self.startProcess(counter, count + 1, parseInt(response.count), step, object, dropdowns);
                            // $('.run-import').text($t('Retry'));
                        }
                        var percent = Math.round(object.percent() * 100) / 100 + step;
                        object.percent(percent);
                        object.percentWidth(percent + '%');
                    }
                ).fail(
                    function (response) {
                        $('.run-import').text($t('Retry'));
                        self.finish(false);
                        self.error(response.message);
                    }
                );
            } else {
                self.finish(true);
                $('.save-import').show();
                return true;
            }
            return true;
        },

        /**
         * Save attribute.
         */
        saveDropdowns: function (dropdownValues) {
            _.each(dropdownValues, function (dropdown) {
                dropdown.chosen = [];

                if (!dropdown.chosenOptions.length) {
                    throw new Error(errorMessage);
                }
                _.each(dropdown.chosenOptions, function (id) {
                    dropdown.chosen.push(_.findWhere(dropdown.options, {
                        id: id
                    }));
                });
            });
            return dropdownValues;
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
                        option['dropdown_id'] = dropdown.dropdown_id;
                        option['dropdown_label'] = dropdown.dropdown_label;
                        tmp.push(_.union(variations, [option]));
                    });
                });

                if (!tmp.length) {
                    return _.map(dropdown.chosen, function (option) {
                        option['dropdown_id'] = dropdown.dropdown_id;
                        option['dropdown_label'] = dropdown.dropdown_label;

                        return [option];
                    });
                }

                return tmp;
            }, []);
        },

        finish: function (bool) {
            var self = this;
            self.end = 1;
            $(".run").attr("disabled", false);
            self.loading(false);

            var object = registry.get(this.name + '.progress_bar.progress');
            object.percent(100);
            object.percentWidth('100%');
            if (bool == false) {
                self.isNotice(false);
                self.isError(true);
            } else {
                self.isNotice(true);
                self.isError(false);
                self.notice($t('The process is over'));
            }
        },
        actionSave: function () {
            var summary = registry.get(this.name + '.generated_products.products');
            summary.force();
        },
        toggleModal: function () {
            this._super();
            this.isNotice(false);
            this.isHref(false);
            this.isError(false);
            $(".debug").html('');
        },
    });
});