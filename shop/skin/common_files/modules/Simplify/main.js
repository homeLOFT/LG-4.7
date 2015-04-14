/* vim: set ts=2 sw=2 sts=2 et: */
/**
 * Simplify module script
 */

ajax.widgets.simplify = function(paymentid, public_key) {

    ajax.widgets.simplify.prototype.paymentid = paymentid;
    ajax.widgets.simplify.prototype.public_key = public_key;

    ajax.widgets.simplify.prototype.isAjaxCheckout = function()
    {
        return (typeof ajax.widgets.checkout !== 'undefined');
    };

    ajax.widgets.simplify.prototype.getAjaxCheckoutObject = function()
    {
        if (ajax.widgets.simplify.prototype.isAjaxCheckout()) {
            return $('.opc-container').get(0).checkoutWidget;
        } else {
            return this;
        }
    };

    ajax.widgets.simplify.prototype.enableCheckoutButton = function()
    {
        $('form[name=checkout_form] button[type=submit]')
            .removeClass('inactive').prop('disabled', false);
    };

    ajax.widgets.simplify.prototype.enablePaymentSelection = function()
    {
        return false;
    };

    ajax.widgets.simplify.prototype.disableCheckoutButton = function()
    {
        $('form[name=checkout_form] button[type=submit]')
            .addClass('inactive').prop('disabled', true);
    };

    ajax.widgets.simplify.prototype.disablePaymentSelection = function()
    {
        return false;
    };

    ajax.widgets.simplify.prototype.valueChangedHandler = function()
    {
        $('form[name=checkout_form] input[name=simplify_token]').remove();
    };

    ajax.widgets.simplify.prototype.simplifyResponseHandler = function(data) {;
        // Re-enable the submit button and payment section
        ajax.widgets.simplify.prototype.getAjaxCheckoutObject().enableCheckoutButton();
        ajax.widgets.simplify.prototype.getAjaxCheckoutObject().enablePaymentSelection();
        // Check for errors
        if (data.error) {
            // Show any validation errors
            if (data.error.code === 'validation') {
                var fieldErrors = data.error.fieldErrors,
                    fieldErrorsLength = fieldErrors.length;
                for (var i = 0; i < fieldErrorsLength; i++) {
                    var fieldCode = fieldErrors[i].field.split('.');
                    if (fieldCode && fieldCode[1]) {
                        fieldCode = fieldCode[1];
                    }
                    $('#cc-' + fieldCode).after('<span class="simplify-error-msg">' + fieldErrors[i].message + '</span>');
                    $('#cc-' + fieldCode).addClass('simplify-error-fld');
                }
            } else {
                xAlert(data.error.message, 'Error code:' + data.error.code);
            }
        } else {
            // The token contains id, last4, and card type
            var token = data['id'];
            // Insert the token into the form so it gets submitted to the server
            $('form[name=checkout_form]').append('<input type="hidden" name="simplify_token" value="' + token + '" />');
            // Submit the form to the server
            $('form[name=checkout_form]').submit();
        }
    };

    ajax.widgets.simplify.prototype.formOnSubmitHandler = function(event) {

        if (
            ajax.widgets.simplify.prototype.isAjaxCheckout()
            && $('form[name=paymentform] input:checked').val() !== ajax.widgets.simplify.prototype.paymentid
        ) {
            // Skip event since a different payment method is selected
            return true;
        }

        if ($('form[name=checkout_form] input[name=simplify_token]').length === 0) {

            // Disable payment section
            ajax.widgets.simplify.prototype.getAjaxCheckoutObject().disablePaymentSelection();
            // Disable checkout button
            ajax.widgets.simplify.prototype.getAjaxCheckoutObject().disableCheckoutButton();

            // Remove all previous errors
            $('#simplify-payment-form .simplify-error-msg').remove();
            $('#simplify-payment-form .simplify-error-fld').removeClass('simplify-error-fld');

            // Remove spaces and dashes
            var prepared_cc_number = String($('#cc-number').val()).replace(/([ -])+/g, '');

            // Generate a card token & handle the response
            SimplifyCommerce.generateToken({
                // public key
                key: ajax.widgets.simplify.prototype.public_key,
                card: {
                    // card info
                    number: prepared_cc_number,
                    expMonth: $('#cc-expMonth').val(),
                    expYear: $('#cc-expYear').val(),
                    cvc: $('#cc-cvc').val(),
                    // address info
                    addressCountry: $('#cc-addressCountry').val(),
                    addressState: $('#cc-addressState').val(),
                    addressCity: $('#cc-addressCity').val(),
                    addressLine1: $('#cc-addressLine1').val(),
                    addressLine2: $('#cc-addressLine2').val(),
                    addressZip: $('#cc-addressZip').val()
                }
            }, ajax.widgets.simplify.prototype.simplifyResponseHandler);

            // Stop propagation until finished
            event.stopImmediatePropagation();

            // Prevent the form from submitting
            return false;
        }

        // Allow form submitting
        return true;
    };

    ajax.widgets.simplify.prototype.bindFormOnSubmit = function() {
        // Add submit handler
        $('form[name=checkout_form]').onFirst('submit', ajax.widgets.simplify.prototype.formOnSubmitHandler);
        // Add on change handlers
        $('#simplify-payment-form input').on('change', ajax.widgets.simplify.prototype.valueChangedHandler);
        $('#simplify-payment-form select').on('change', ajax.widgets.simplify.prototype.valueChangedHandler);
    };

    ajax.widgets.simplify.prototype.unBindFormOnSubmit = function() {
        // Remove submit handler
        $('form[name=checkout_form]').off('submit', ajax.widgets.simplify.prototype.formOnSubmitHandler);
        // Remove on change handlers
        $('#simplify-payment-form input').on('change', ajax.widgets.simplify.prototype.valueChangedHandler);
        $('#simplify-payment-form select').on('change', ajax.widgets.simplify.prototype.valueChangedHandler);
    };

    // Finally bind handlers
    ajax.widgets.simplify.prototype.bindFormOnSubmit();
};
