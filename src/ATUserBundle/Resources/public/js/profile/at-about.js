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
    }).done(function (data) {
        if (data.error) {
            toastr['error'](data.message);
        } else {
            if (data.message) {
                toastr['success'](data.message);
            }
            // update form's inputs
            for (inputTarget in data.data) {
                $('#pmb-view-' + inputTarget).html((data.data[inputTarget]));
            }
            // close edition block
            var t = $form.data('profile-pmb-block-target');
            $(t).removeClass('toggled');
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        var responseJSON = jqXHR.responseJSON;

        for (fieldErrorName in responseJSON.error) {
            var inputField = $('input[name*=' + fieldErrorName + ']');
            inputField.parent().addClass("has-error");
            inputField.after('<small class="help-block">' + responseJSON.error[fieldErrorName] + '</small>')
        }
    });

});