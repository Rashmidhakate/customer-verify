define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'uiRegistry',
    'Magento_Checkout/js/model/postcode-validator',
    'mage/url',
    'mage/storage',
    'Magento_Ui/js/model/messageList',
    'mage/translate'
], function ($, ko, Component, quote, uiRegistry, postcodeValidator, url, storage, globalMessageList , $t) {
    'use strict';
    var url = url.build('game/flow/filter');
    //console.log(url);
    return Component.extend({
        defaults: {
            selectedValue : ko.observable(),
            specificValue : ko.observableArray([]),
            filteredItems : ko.observableArray([]),
            availableAddresses : ko.observableArray(window.checkoutConfig.assembler_config),
            template: 'Brainvire_Customization/checkout/shipping/assembler'
        },
        initialize: function () {
            var self = this;
            self._super();
            quote.shippingAddress.subscribe(function (data) {
                if(!quote.shippingAddress().postcode && quote.shippingAddress().postcode == null){
                    self.selectedValue('');
                }   
                var country_id = quote.shippingAddress().countryId;
                var payload = JSON.stringify({
                    postcode :quote.shippingAddress().postcode
                });

                storage.post(
                    url, payload, false
                ).done(function (result) {
                    self.availableAddresses(result.response);
                }).fail(function (result) {
                    globalMessageList.addErrorMessage({
                        'message': $t('Could not load data. Please try again later')
                    });
                });
            });
            
        },
        initObservable: function () {
            return this;
        },
        selectedMethod: ko.computed(function () {
            return quote.shippingAddress() ? quote.shippingAddress().postcode : null;
        }),
    });
});