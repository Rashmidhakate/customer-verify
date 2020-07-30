var config = {
    map: {
        '*': {
        	 'Magento_Checkout/js/model/postcode-validator':'Brainvire_Customization/js/model/postcode-validator',
        	 'Magento_Checkout/js/view/shipping':'Brainvire_Customization/js/view/shipping'
        }
    },
	config: {
		mixins: {
		    'Magento_Checkout/js/action/set-shipping-information': {
		        'Brainvire_Customization/js/order/set-shipping-information-mixin': true
		    },
			// 'Magento_Checkout/js/action/place-order': {
			// 	'Brainvire_Customization/js/order/place-order-mixin': true
			// },
			// 'Magento_Checkout/js/action/set-payment-information': {
			// 	'Brainvire_Customization/js/order/set-payment-information-mixin': true
			// },
			// 'Magento_Checkout/js/view/shipping': {
			// 	'Brainvire_Customization/js/view/shipping': true
			// },
		}
	}
};