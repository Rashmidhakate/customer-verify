/**
 * @author aakimov
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();
            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }
            if(jQuery('#assembler_id').val()){
                shippingAddress['extension_attributes']['assembler_id'] = jQuery('#assembler_id').val();   
            }
            // you can extract value of extension attribute from any place (in this example I use customAttributes approach)
            
            //shippingAddress['extension_attributes']['store_address'] = jQuery('[name="selectedaddress"]').val();
            // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            return originalAction();
        });
    };
});