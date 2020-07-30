define([
    'jquery',
    'mage/utils/wrapper',
    'Brainvire_Customization/js/order/storeaddress-assigner'
], function ($, wrapper, storeaddressAssigner) {
    'use strict';

    return function (placeOrderAction) {

        /** Override default place order action and add comments to request */
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            storeaddressAssigner(paymentData);

            return originalAction(paymentData, messageContainer);
        });
    };
});