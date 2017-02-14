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
                template: 'PurchasedAt_Magento2Payment/payment/purchasedat'
            },

            /** Returns send check to info
             * window.checkoutConfig.payment.purchasedat.foobar is contains the config array foobar fields value from the PurchasedatConfigProvider.php
             * So if we need more datas pass from php to javascript, we need more fields into the config array in the configprovider, and we reach it same way like foobar example
             * @returns {*}
             */
            getInstructions: function() {
                return window.checkoutConfig.payment.purchasedat.instructions;
            },

            getPayButtonParams: function() {
                return window.checkoutConfig.payment.purchasedat.params;
            },

            getPayButtonTarget: function() {
                return window.checkoutConfig.payment.purchasedat.target;
            },

            getLogoURL: function() {
                return window.checkoutConfig.payment.purchasedat.logo_url;
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
                if ($(".payment-method-title #purchasedat").prop("checked")) {
                    $.ajax({
                        url: window.checkoutConfig.payment.purchasedat.ajax_url,
                        data: {"email": quote.guestEmail},
                        cache: false,
                        dataType: 'json'
                    }).done(function (data) {
                        var params = data.token;
                        var target_string = data.target;
                        if (params != "") {
                            var params_array = {token: params, target: target_string};
                            purchased_at.auto(params_array);
                        }
                        else {
                            alert("Error in purchased.at service call, get empty params field");
                        }
                    }).fail(function () {
                        alert("Error in ajax call: " + window.checkoutConfig.payment.purchasedat.ajax_url + "?email=" + quote.guestEmail);
                    });
                }
                return false;
            }
        });
    }
);
