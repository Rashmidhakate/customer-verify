/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mageUtils'
], function ($,utils) {
    'use strict';
    //console.log(select.checkZipcode());
    return {
        validatedPostCodeExample: [],

        /**
         * @param {*} postCode
         * @param {*} countryId
         * @param {Array} postCodesPatterns
         * @return {Boolean}
         */
        validate: function (postCode, countryId, postCodesPatterns) {
            //console.log($('input[name="postcode"]').val());
            
            
            if(postCode != null){
                $('.custom-shipping-method-fields-shipping-information').css("display","block");
            }
            if(!postCode){
                $('.custom-shipping-method-fields-shipping-information').css("display","none");
            }
            var pattern, regex,
                patterns = postCodesPatterns ? postCodesPatterns[countryId] :
                    window.checkoutConfig.postCodes[countryId];

            this.validatedPostCodeExample = [];

            if (!utils.isEmpty(postCode) && !utils.isEmpty(patterns)) {
                for (pattern in patterns) {
                    if (patterns.hasOwnProperty(pattern)) { //eslint-disable-line max-depth
                        this.validatedPostCodeExample.push(patterns[pattern].example);
                        regex = new RegExp(patterns[pattern].pattern);

                        if (regex.test(postCode)) { //eslint-disable-line max-depth
                            return true;
                        }
                    }
                }

                return false;
            }

            return true;
        },
    };
});
