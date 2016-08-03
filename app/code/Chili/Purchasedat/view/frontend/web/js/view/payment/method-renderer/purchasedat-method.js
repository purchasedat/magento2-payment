/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'purchased_at'
    ],
    function ($,Component, additionalValidators, quote, customerData) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Chili_Purchasedat/payment/purchasedat'
            },

            /** Returns send check to info */
            getInstructions: function() {
                this.preparePurchasedat();
                return window.checkoutConfig.payment.purchasedat.instructions;
            },

            getPayButtonParams: function() {
                return window.checkoutConfig.payment.purchasedat.params;
            },

            getPayButtonTarget: function() {
                return window.checkoutConfig.payment.purchasedat.target;
            },

            getEmail: function () {
                if(quote.guestEmail) return quote.guestEmail;
                else return window.checkoutConfig.customerData.email;
            },

            /** Redirect to purchased.at */
            continueToPurchasedat: function () {
                if (additionalValidators.validate()) {
                    customerData.invalidate(['cart']);
                    this.selectPaymentMethod();
                    return false;
                }
            },

            preparePurchasedat: function () {
/*                if ($("#purchasedat_submit").is(":visible")) {
                    var params = window.checkoutConfig.payment.purchasedat.params;
                    var target_string = window.checkoutConfig.payment.purchasedat.target;
                    var params_array = {token: params, target: target_string}
                    purchased_at.auto(params_array) ;
                }
                else {*/
                    $.ajax({
                        url: window.checkoutConfig.payment.purchasedat.ajax_url,
                        data: {"email": quote.guestEmail},
                        cache: false,
                        dataType: 'json'
                    }).done(function (data) {
                        var params = data.token;
                        var target_string = data.target;
                        var params_array = {token: params, target: target_string};
                        purchased_at.auto(params_array);
                    });
//                }
                return false;
            }
        });
    }
);
