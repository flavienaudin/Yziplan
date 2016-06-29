/**
 * Created by Flavien on 07/06/2016.
 */

$(document).ready(function () {
    /*
     * Profile About Edit Toggle
     */
    if ($('[data-profile-action]')[0]) {
        $('body').on('click', '[data-profile-action]', function (e) {
            e.preventDefault();
            var d = $(this).data('profile-action');
            var t = $(this).data('profile-action-target');

            if (d === "edit") {
                $(t).toggleClass('toggled');
            }
            if (d === "reset") {
                $(t).removeClass('toggled');
            }
        });
    }
});

$('form').on("submit", function (e) {
    e.preventDefault();
    var $form = $(this);
    $('.has-error').each(function () {
        $(this).find("small.help-block").remove();
        $(this).removeClass("has-error");
    });
    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: $form.serialize()
    }).done(function (responseData, textStatus, jqXHR) {
        // update form's inputs
        if (responseData.data) {
            for (var inputTarget in responseData.data) {
                if (responseData.data.hasOwnProperty(inputTarget)) {
                    $('#pmb-view-' + inputTarget).html((responseData.data[inputTarget]));
                }
            }
        }
        // close edition block
        var t = $form.data('profile-pmb-block-target');
        $(t).removeClass('toggled');
    }).fail(function (jqXHR, textStatus, errorThrown) {
        var responseJSON = jqXHR.responseJSON;
        if (responseJSON.formErrors) {
            var formErrors = responseJSON.formErrors;
            for (var fieldErrorName in formErrors) {
                if (formErrors.hasOwnProperty(fieldErrorName)) {
                    var inputField = $('input[name*=' + fieldErrorName + ']');
                    inputField.parent().addClass("has-error");
                    inputField.after('<small class="help-block">' + responseJSON.formErrors[fieldErrorName] + '</small>')
                }
            }
        }
    }).always(function (responseDataOrJSON) {
        if (responseDataOrJSON.responseJSON) {
            // In case of fail()
            responseDataOrJSON = responseDataOrJSON.responseJSON;
        }
        if (responseDataOrJSON.messages) {
            var messages = responseDataOrJSON.messages;
            for (var messageType in messages) {
                if (messages.hasOwnProperty(messageType)) {
                    messages[messageType].forEach(function (mess) {
                        toastr[messageType](mess);
                    });
                }
            }
        }
    });

});