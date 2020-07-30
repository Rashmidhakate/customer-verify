define([
    'jquery'
], function ($) {
    'use strict';


    /** Override default place order action and add comment to request */
    return function (paymentData) {

        if (paymentData['extension_attributes'] === undefined) {
            paymentData['extension_attributes'] = {};
        }
        if(jQuery('#assembler_id').val()){
        	paymentData['extension_attributes']['assembler_id'] = jQuery('#assembler_id').val();
        }
        
    };
});