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
            getMailingAddress: function() {
                return window.checkoutConfig.payment.purchasedat.mailingAddress;
            },

            getPayButton: function() {
                return window.checkoutConfig.payment.purchasedat.payButton;
            },

            getPayButtonWidget: function() {
                return window.checkoutConfig.payment.purchasedat.widgetUrl;
            },

            getPayButtonParams: function() {
                return window.checkoutConfig.payment.purchasedat.params;
            },

            getPayButtonTarget: function() {
                return window.checkoutConfig.payment.purchasedat.target;
            },

            /** Redirect to paypal */
            continueToPurchasedat: function () {
                if (additionalValidators.validate()) {
                    customerData.invalidate(['cart']);
/*                            $.mage.redirect(
                        window.checkoutConfig.payment.purchasedat.redirectUrl[quote.paymentMethod().method]
                    );*/
/*                    var params = $("#purchased_at_params").val() ;
                    var target_string = $("#purchased_at_target").val() ;
                    var obj = JSON.stringify({ token: params, target: target_string }) ;
                    purchased_at.auto(obj) ;*/
                    return false;
                }
            },

            preparePurchasedat: function () {
                var params = $("#purchased_at_params").val() ;
                var target_string = $("#purchased_at_target").val() ;
                var params_array = {token: params, target: target_string}
                purchased_at.auto(params_array) ;
                return false;
            }

        });
    }
);