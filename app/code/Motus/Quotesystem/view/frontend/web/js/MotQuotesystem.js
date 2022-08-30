/**
 * Motus Quotesystem
 */
/*jshint jquery:true*/
define(
    [
    "jquery",
    'mage/template',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'Magento_Customer/js/customer-data',
    "Magento_Ui/js/modal/modal",
    "jquery/ui",
    'mage/validation',
    "jquery/file-uploader"
    ], function ($, mageTemplate, $t, alert, confirm, customerData, modal) {
        'use strict';
        $.widget(
            'mage.MotQuotesystem', {
                options: {
                    backUrl: '',
                    confirmMessageForEditQuote: $t("Are you sure you want to edit this quote?"),
                    errorNoCheckBoxChecked: $t('No Checkbox is checked.'),
                    confirmMessageForDeleteQuote: $t('Are you sure you want to delete these quotes?'),
                    confirmMessageForsingleDeleteQuote: $t('Are you sure you want to delete this quote?'),
                    errorRequestedQuantity: $t('Requested Amount is not available please contact Admin'),
                    quotedoesnotexists: $t('Requested Quote does not exist, please contact the admin.'),
                    errorQuoteItemAlreadyInCart: $t('A Quote item of same product is already in cart.'),
                    ajaxErrorMessage: $t('There is some error during executing this process, please try again later.'),
                    addToQuoteButtonTextWhileAdding: '',
                    addToQuoteButtonDisabledClass: 'disabled',
                    addToQuoteButtonTextAdded: '',
                    addToQuoteButtonTextDefault: '',
                    categoryListAction: '',
                    categoryListItem : '',
                    addQuoteModel:null
                },
                _create: function () {
                    var self = this;
                    var dataForm = $(self.options.quoteForm);
                    var html = $(self.options.quoteButtonHtml);
                    var showCart = self.options.showCart;
                    if ($(self.options.addToCartAction).length > 0) {
                        if ($(self.options.paypal).length >0) {
                            $(html).css("float", "left");
                            $(self.options.addToCartAction).append(html);
                        } else {
                            $(self.options.addToCartAction).append(html);
                        }
                    } else {
                        $(self.options.productAddToCartForm).append(html);
                    }

                    if (!showCart && $(html).length > 0) {
                        $('.action.primary.tocart').remove();
                    }
                    var popoverhtml = $(self.options.popoverbackgroundhtml);
                    $(self.options.productAddToCartForm).append(popoverhtml);
                    var productForm = $(self.options.productAddToCartForm);
                    productForm.mage('validation', {});
                    var formAction = $(self.options.productAddToCartForm).attr("action");
                    dataForm.mage('validation', {});
                    $(self.options.quoteedit).on(
                        'click', function () {
                            var element = $(this);
                            var dicision = confirm(
                                {
                                    content: self.options.confirmMessageForEditQuote,
                                    actions: {
                                        confirm: function () {
                                             var $url=$(element).attr('data-url');
                                            window.location = $url;
                                        },
                                    }
                                }
                            );
                        }
                    );

                    $(self.options.reset).on('click', function(){
                        $(".mot-uploaded-file").remove();
                    })

                    $(self.options.massdelete).click(
                        function (e) {
                            e.preventDefault();
                            var flag =0;
                            $(self.options.quotecheckbox).each(
                                function () {
                                    if (this.checked == true) {
                                        flag =1;
                                    }
                                }
                            );
                            if (flag == 0) {
                                alert(
                                    {
                                        content: self.options.errorNoCheckBoxChecked
                                    }
                                );
                                return false;
                            } else {
                                var dicision = confirm(
                                    {
                                        content: self.options.confirmMessageForDeleteQuote,
                                        actions: {
                                            confirm: function () {
                                                $(self.options.massdeleteform).submit();
                                            },
                                        }
                                    }
                                );
                            }
                        }
                    );

                    $(self.options.selectall).click(
                        function (event) {
                            if (this.checked) {
                                $(self.options.quotecheckbox).each(
                                    function () {
                                        this.checked = true;
                                    }
                                );
                            } else {
                                $(self.options.quotecheckbox).each(
                                    function () {
                                        this.checked = false;
                                    }
                                );
                            }
                        }
                    );

                    $(self.options.quotedelete).click(
                        function () {
                            var element = $(this);
                            var dicision = confirm(
                                {
                                    content: self.options.confirmMessageForsingleDeleteQuote,
                                    actions: {
                                        confirm: function () {
                                            var $url=$(element).attr('data-url');
                                            window.location = $url;
                                        },
                                    }
                                }
                            );
                        }
                    );

                    $(self.options.quotestatus).on(
                        "click", function () {
                            self.ajaxRequestForAddToCart(this);
                        }
                    );

                    $(self.options.saveButton).on(
                        "click",function () {
                            if ($(self.options.quoteForm).valid()!=false) {
                                $(self.options.saveButton).attr("disabled","disabled");
                                $(self.options.quoteForm).submit();
                            }
                        }
                    );
                    $(self.options.switchOption).on(
                        "click",function () {
                            if ($(this).is(":checked")) {
                                $(self.options.quotePrice).removeAttr("disabled");
                                $(self.options.quotePrice).val(self.options.price);
                                $(self.options.quoteQuantity).removeAttr("disabled");
                            } else {
                                $(self.options.quoteQuantity).attr("disabled","disabled");
                                $(self.options.quotePrice).attr("disabled","disabled");
                                $(self.options.quotePrice).val(self.options.formatPrice);
                            }
                        }
                    );
                    $(self.options.quoteButtonHtml).on(
                        "click",function () {
                            $('body').trigger('processStart');
                            var customer = customerData.get('customer');
                            if (customer().firstname == false || customer().firstname == undefined) {
                                if ($('body').find('a.proceed-to-checkout').length) {
                                    $('body').trigger('processStop');
                                    $('body').find('a.proceed-to-checkout').trigger('click');
                                } else {
                                    self.updateCustomerData();
                                }
                            } else {
                                $('body').trigger('processStop');
                                if ($(self.options.productAddToCartForm).valid()!=false) {
                                    $(self.options.popoverbackgroundhtml).find('.mot-mp-model-popup').addClass('_show');
                                    $(self.options.popoverbackgroundhtml).show();
                                }
                            }
                        }
                    );
                    $(self.options.popOverclose).on(
                        "click",function () {
                            $('.mot-uploaded-file-del').trigger('click');
                            $(self.options.productAddToCartForm).attr("action",formAction);
                            $(self.options.popoverbackgroundhtml).hide();
                        }
                    );

                    // for category page product quote popup close action
                    $('body').on(
                        "click", '.action-close', function () {
                            if ($(this).parents('.modal-inner-wrap').find('#mot-qs-ask-data').length) {
                                $('.mot-uploaded-file-del').trigger('click');
                                $(this).parents('.modal-inner-wrap').find('.reset').trigger('click');
                                $(self.options.popoverbackgroundhtml).hide();
                            }
                        }
                    );

                    $(self.options.submitButton).on(
                        'click', function () {
                            self.submitQuote(this);
                        }
                    );

                    $(self.options.quoteStatus).on(
                        'change', function () {
                            var status = $(this).val();
                            if (status == 1) {
                                $(self.options.quoteMinQuantity).addClass('required-entry');
                            } else {
                                $(self.options.quoteMinQuantity).removeClass('required-entry');
                            }
                        }
                    );

                    $(self.options.productitems).each(
                        function () {
                            var product = $(this);
                            var quoteData = $(self.options.quoteProductData);
                            var productId = product.find('.price-box').attr('data-product-id');
                            if (quoteData!==undefined && quoteData[0]!==undefined && quoteData[0][productId]!==undefined) {
                                self.addQuoteButton(product, quoteData[0][productId], productId, showCart);
                            }
                        }
                    );
                    $('.sort').on(
                        'click', function () {
                            $("body").append($("<div/>").addClass("mot_qs_front_loader").css("height",$(window).width()).append($("<div/>")));
                            var self = $(this).find('.fa');
                            var className = self.attr('class');
                            var name = self.attr('name');
                            var sortingType = self.attr('sortingType');
                            var value = name + sortingType;
                            if (className == "fa fa-sort") {
                                self.removeClass("fa fa-sort").addClass("fa fa-sort-asc")
                                self.attr("sortingType", "Inc");
                                $('#sortingStatus').attr("value", name+"Inc");
                                $('#sortingType').attr("value","Desc");
                                $('#save').trigger("click");
                            } else if (className == "fa fa-sort-asc") {
                                self.removeClass("fa fa-sort-asc").addClass("fa fa-sort-desc")
                                self.attr("sortingType", "Desc");
                                $('#sortingStatus').attr("value", name+"Desc");
                                $('#sortingType').attr("value","Inc");
                                $('#save').trigger("click");
                            } else {
                                self.removeClass("fa fa-sort-desc").addClass("fa fa-sort-asc")
                                self.attr("sortingType", "Inc");
                                $('#sortingStatus').attr("value", name+"Inc");
                                $('#sortingType').attr("value","Desc");
                                $('#save').trigger("click");
                            }
                        }
                    );
                    $('body').delegate(
                        ".quotesystem_cat_add", 'click', function () {
                            $(".mot-uploaded-file").remove();
                            self.options.categoryListItem = $(this);
                            self.options.categoryListAction = 'popup';
                            self.checkAndAddToQuote($(this), 'popup');
                        }
                    );
                    $('body').delegate(
                        ".quotesystem_redirect", 'click', function () {
                            self.options.categoryListItem = $(this);
                            self.options.categoryListAction = 'redirect';
                            self.checkAndAddToQuote($(this), 'redirect');
                        }
                    );
                    $(self.options.categorySubmitButton).on(
                        'click', function () {
                            var form = $(this).parents('form');
                            if ($(form).valid()!=false) {
                                self.categorySubmitQuote(form);
                            }
                        }
                    );
                    $('#mot-file-field').fileupload(
                        {
                            dataType: 'json',
                            sequentialUploads: true,
                            acceptFileTypes: /(\.|\/)(gif|jpe?g|png|pdf|doc|zip)$/i,
                            add: function (e, data) {
                                var progressTmpl = mageTemplate('#mot-file-field-uploader-template'),
                                tmpl;
                                var thisObj = $(this);

                                $.each(
                                    data.files, function (index, file) {
                                        data.fileId = Math.random().toString(33).substr(2, 18);

                                        tmpl = progressTmpl(
                                            {
                                                data: {
                                                    id: data.fileId
                                                }
                                            }
                                        );

                                        if ($('.mot-uploaded-file').length) {
                                            var indexKey = 1;
                                            $('.mot-uploaded-file').each(
                                                function () {
                                                    if (indexKey == 1) {
                                                        $(this).before(tmpl);
                                                    }
                                                    indexKey++;
                                                }
                                            );
                                        } else {
                                            $(tmpl).appendTo('.mot-file-field-container');
                                        }
                                    }
                                );

                                thisObj.fileupload('process', data).done(
                                    function () {
                                        data.submit();
                                    }
                                );
                            },
                            done: function (e, data) {
                                if (data.result && !data.result.error) {
                                    var progressTmpl = mageTemplate('#mot-file-field-template'),
                                    tmpl;
                                    tmpl = progressTmpl(
                                        {
                                            data: {
                                                name: data.result.name,
                                                file: data.result.file,
                                                extension: data.result.type
                                            }
                                        }
                                    );

                                    $(tmpl).appendTo('.mot-file-field-container');
                                } else {
                                    $('#' + data.fileId)
                                    .delay(2000)
                                    .hide('highlight');
                                    alert(
                                        {
                                            content: $.mage.__('We don\'t recognize or support this file extension type.')
                                        }
                                    );
                                }
                                $('#' + data.fileId).remove();
                            },
                            progress: function (e, data) {
                                var progress = parseInt(data.loaded / data.total * 100, 10);
                                var progressSelector = '#' + data.fileId + ' .progressbar-container .progressbar';
                                $(progressSelector).css('width', progress + '%');
                            },
                            fail: function (e, data) {
                                var progressSelector = '#' + data.fileId;
                                $(progressSelector).removeClass('upload-progress').addClass('upload-failure')
                                .delay(2000)
                                .hide('highlight')
                                .remove();
                            }
                        }
                    );
                    $('#mot-file-field').fileupload(
                        'option', {
                            process: [{
                                action: 'load',
                                fileTypes: /^image\/(gif|jpe?g|png|pdf|doc|zip)$/
                            }, {
                                action: 'resize',
                                maxWidth: self.options.maxWidth ,
                                maxHeight: self.options.maxHeight
                            }, {
                                action: 'save'
                            }]
                        }
                    );

                    $('.mot-file-field-container').on(
                        "click", ".mot-uploaded-file-del", function () {
                            var thisObj = $(this);
                            var fileName = $(this).parent('.mot-uploaded-file').find('.mot-uploaded-file-value').val();
                            $.ajax(
                                {
                                    url: self.options.fileDeleteUrl,
                                    data: { file_name : fileName },
                                    type: "post",
                                    datatype: "json",
                                    showLoader: true,
                                    success: function (data) {
                                        thisObj.parent('.mot-uploaded-file').remove();
                                    },
                                    error: function (data) {
                                        thisObj.parent('.mot-uploaded-file').remove();
                                    }
                                }
                            );
                        }
                    );
                },
                submitQuote:function (this_this) {
                    var self = this;
                    var productForm = $(self.options.productAddToCartForm);
                    if ($(self.options.productAddToCartForm).valid()!==false) {
                        $('body').trigger('processStart');
                        $(this_this).text($t("Saving")+'..');
                        $(this_this).css('opacity','0.7');
                        $(this_this).css('cursor','default');
                        $(this_this).attr('disabled','disabled');
                        var action = self.options.saveQuoteUrl;
                        $(self.options.productAddToCartForm).attr("action",action);
                        $(this_this).removeAttr("onclick");
                        productForm.submit();
                    }
                },
                ajaxRequestForAddToCart:function (e) {
                    var self = this;
                    $("body").append($("<div/>").addClass("mot_qs_front_loader").css("height",$(window).width()).append($("<div/>")));
                    var quoteId = $(e).parents("td").siblings(".id").val();
                    var quoteQty = $(e).parents("td").siblings(".mot_qs_quote_qty").find("span").text();
                    var quotePrice = $(e).parents("td").siblings(".mot_qs_quote_price").val();
                    $.ajax(
                        {
                            url         :   self.options.addtocarturl,
                            data        :   {quote_id:quoteId,quote_qty:quoteQty,quote_price:quotePrice},
                            type        :   "post",
                            datatype    :   "html",
                            success     :   function (data) {
                                console.log(data);
                                if (data.error===1) {
                                    alert(
                                        {
                                            content: data.message
                                        }
                                    );
                                } else {
                                    alert(
                                        {
                                            content: data.message
                                        }
                                    );
                                    if (data.redirecturl!='') {
                                        document.location.href = data.redirecturl;
                                    }
                                }
                                $("body").find(".mot_qs_front_loader").remove();

                            },
                            error: function (data) {
                                alert(
                                    {
                                        content: self.options.ajaxErrorMessage
                                    }
                                );
                                $("body").find(".mot_qs_front_loader").remove();
                            }
                        }
                    );
                },
                ajaxRequestToSaveQuote:function (dataForm, action) {
                    var self = this;
                    $(self.options.popoverbackgroundhtml).hide();
                    $("body").append($("<div/>").addClass("mot_qs_front_loader").css("height",$(window).width()).append($("<div/>")));
                    $.ajax(
                        {
                            url         :   action,
                            data        :   dataForm,
                            type        :   "post",
                            datatype    :   "html",
                            success     :   function (data) {
                                if (data.error===1) {
                                    alert(
                                        {
                                            content: data.message
                                        }
                                    );
                                } else {
                                    alert(
                                        {
                                            content: data.message
                                        }
                                    );
                                    if (data.redirecturl!='') {
                                        // document.location.href = data.redirecturl;
                                    }
                                }
                                $("body").find(".mot_qs_front_loader").remove();

                            },
                            error: function (data) {
                                alert(
                                    {
                                        content: self.options.ajaxErrorMessage
                                    }
                                );
                                $("body").find(".mot_qs_front_loader").remove();
                            }
                        }
                    );
                },
                addQuoteButton:function (currentObject, quoteData, productId, showCart) {
                    var self = this;
                    if (quoteData['status']===1) {
                        var html = "<div class='actions-primary quote_button'>"+
                        "<button type='button' title='Add Quote' class='quotesystem_cat_add action toquote primary' data-product-id='"+productId+"' data-qty='"+quoteData['min_qty']+"' data-url='"+quoteData['url']+"'>"+
                        "<span>"+$t("Add to Quote")+"</span>"+
                        "</button></a></div>";
                    } else {
                        var html = "<div class='actions-primary quote_button'>"+
                        "<button type='button' title='Add Quote' class='quotesystem_redirect action toquote primary' data-url='"+quoteData['url']+"'>"+
                        "<span>"+$t("Add to Quote")+"</span>"+
                        "</button></div>";
                    }
                    currentObject.find(".product-item-actions .actions-primary").after(html);
                    if (!showCart) {
                        currentObject.find(".action.tocart.primary").remove();
                    }
                },
                categorySubmitQuote: function (form) {
                    var self = this;
                    self.ajaxSubmit(form);
                },
                /**
                 * @param {String} form
                 */
                disableAddToQuoteButton: function (form) {
                    var addToQuoteButtonTextWhileAdding = this.options.addToQuoteButtonTextWhileAdding || $t('Adding...'),
                    addToQuoteButton = $(form).find('.submit_button');
                    addToQuoteButton.addClass(this.options.addToQuoteButtonDisabledClass);
                    addToQuoteButton.find('span').text(addToQuoteButtonTextWhileAdding);
                    addToQuoteButton.attr('title', addToQuoteButtonTextWhileAdding);
                },
                /**
                 * @param {String} form
                 */
                ajaxSubmit: function (form) {
                    var self = this;
                    self.disableAddToQuoteButton(form);
                    $.ajax(
                        {
                            url: form.attr('action'),
                            data: form.serialize(),
                            type: 'post',
                            dataType: 'json',
                            /**
                             * @inheritdoc 
                             */
                            beforeSend: function () {
                                $('body').trigger('processStart');
                            },
                            /**
                             * @inheritdoc 
                             */
                            success: function (res) {
                                var eventData, parameters;
                                $('body').trigger('processStop');
                                self.enableaddToQuoteButton(form);
                                $(form)[0].reset();
                                $(self.options.addQuoteModel).modal('closeModal');
                            }
                        }
                    );
                },
                /**
                 * @param {String} form
                 */
                enableaddToQuoteButton: function (form) {
                    var addToQuoteButtonTextAdded = this.options.addToQuoteButtonTextAdded || $t('Added'),
                    self = this,
                    addToQuoteButton = $(form).find('.submit_button');
                    addToQuoteButton.find('span').text(addToQuoteButtonTextAdded);
                    addToQuoteButton.attr('title', addToQuoteButtonTextAdded);
                    setTimeout(
                        function () {
                            var addToQuoteButtonTextDefault = self.options.addToQuoteButtonTextDefault || $t('Add to Quote');
                            addToQuoteButton.removeClass(self.options.addToCartButtonDisabledClass);
                            addToQuoteButton.find('span').text(addToQuoteButtonTextDefault);
                            addToQuoteButton.attr('title', addToQuoteButtonTextDefault);
                        }, 1000
                    );
                },
                addQuoteFormToPage: function (element) {
                    var self = this;
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        validation:{},
                        title: "Confirm Quote details", //write your popup title
                        buttons: [
                        {
                            text: $.mage.__('Submit'),
                            class: 'button',
                            click: function () {
                                var form = $('#quotesystem_quote_add_cat');
                                if ($(form).validation() && $(form).validation('isValid')) {
                                    self.categorySubmitQuote(form);
                                }
                            }
                        },
                        {
                            text: $.mage.__('Reset'),
                            class: 'reset',
                            click: function () {
                                var form = $('#quotesystem_quote_add_cat');
                                $(form)[0].reset();
                                $(".mot-uploaded-file").remove();
                            }
                        }
                        ]
                    };
                    // manage current product details
                    var parentelement = $(element).parents('.product-item-actions').find('form[data-role="tocart-form"]');
                    $(parentelement).find('input[type="hidden"], input[type="text"]').each(
                        function () {
                            var elem = $(this).clone();
                            var elemValue = $(this).val();
                            var elemName = $(elem).attr('name');
                            if ($(self.options.popoverbackgroundhtml).find('input[name="'+elemName+'"]').length) {
                                $(self.options.popoverbackgroundhtml).find('input[name="'+elemName+'"]').remove();
                                $(self.options.popoverbackgroundhtml).find('form').append(elem);
                                $(elem).attr('value',elemValue);
                            } else {
                                $(self.options.popoverbackgroundhtml).find('form').append(elem);
                                $(elem).attr('value',elemValue);
                            }
                        }
                    );
                    var proName = $.trim($(parentelement).parents('.product-item-details').find('.product.name.product-item-name a').text());
                    var productId = $(element).attr('data-product-id');
                    $(self.options.popoverbackgroundhtml).find('input[name="product_name"]').attr('value', proName);
                    $(self.options.popoverbackgroundhtml).find('input[name="product"]').attr('value', productId);
                    $(self.options.popoverbackgroundhtml).find('.mot-qs-min-qty').html($t('minimum quote quantity is ')+$(element).attr('data-qty'));
                    $(self.options.popoverbackgroundhtml).find('#quote_qty').addClass('validate-digits-range digits-range-'+$(element).attr('data-qty')+'-');

                    self.options.addQuoteModel = $(self.options.popoverbackgroundhtml);
                    modal(options, $(self.options.addQuoteModel));
                    $(self.options.addQuoteModel).modal('openModal');
                },
                checkAndAddToQuote: function (element, type) {
                    if (type!=='') {
                        var self = this;
                        $('body').trigger('processStart');
                        var customer = customerData.get('customer');
                        if (customer().firstname == false || customer().firstname == undefined) {
                            self.updateCustomerData();
                        } else {
                            if (type=='redirect') {
                                window.location = $(element).attr('data-url');
                            } else {
                                self.validateAddToCartForm(element);
                                $('body').trigger('processStop');
                            }
                        }
                    }
                },
                updateCustomerData:function () {
                    var self = this;
                    customerData.reload([], true).done(
                        function (sections) {
                            var customername = sections.customer.firstname;
                            if (customername == undefined) {
                                $('body').trigger('processStop');
                                $('body').find('a.proceed-to-checkout').trigger('click');
                            } else {
                                $('body').trigger('processStop');
                                if ($(self.options.quoteButtonHtml).length) {
                                    $(self.options.quoteButtonHtml).trigger('click');
                                } else {
                                    self.checkAndAddToQuote($(self.options.categoryListItem), self.options.categoryListAction);
                                }
                            }
                        }
                    );
                },
                validateAddToCartForm: function (element) {
                    var self = this;
                    var addToCartForm = $(element).parents('.product.actions.product-item-actions').find('form[data-role="tocart-form"]');
                    if ($(addToCartForm).validation() && $(addToCartForm).validation('isValid')) {
                        $('body').trigger('processStop');
                        self.addQuoteFormToPage(element);
                    }
                    $('body').trigger('processStop');
                }
            }
        );
        return $.mage.MotQuotesystem;
    }
);
