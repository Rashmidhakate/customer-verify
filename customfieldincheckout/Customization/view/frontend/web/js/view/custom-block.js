define(
    [
        'jquery',
        'knockout',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Brainvire_Customization/js/form/element/select'
    ],
    function(
        $,
        ko,
        Component,
        quote,
        select
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                assemblerValue : ko.observable(),
                visibleData : ko.observable(false),
                template: 'Brainvire_Customization/checkout/payment/custom-block'
            },

            initialize: function () {
                var self = this;
                this._super();
                select().selectedValue.subscribe(function(data){
                    if(select().selectedValue() && select().selectedValue() == 1){
                        self.visibleData(true);
                    }else{
                        self.visibleData(false);
                    }
                });
            },

            isVisibleAssemblerButton : function (data) {
               return this.visibleData();
            },

        });
    }
);