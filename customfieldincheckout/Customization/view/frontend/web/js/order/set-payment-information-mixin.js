define([
    'jquery',
    'mage/utils/wrapper',
    'Brainvire_Customization/js/order/storeaddress-assigner'
], function ($, wrapper, storeaddressAssigner) {
    'use strict';

    return function (placeOrderAction) {

        /** Override place-order-mixin for set-payment-information action as they differs only by method signature */
        return wrapper.wrap(placeOrderAction, function (originalAction, messageContainer, paymentData) {
            storeaddressAssigner(paymentData);

            return originalAction(messageContainer, paymentData);
        });
    };
});