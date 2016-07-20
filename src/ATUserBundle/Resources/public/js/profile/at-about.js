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
    var $form = $(this);
    ajaxFormSubmission(this, e, function (responseJSON, textStatus, jqXHR) {
        // update form's inputs
        if (responseJSON.data) {
            for (var inputTarget in responseJSON.data) {
                if (responseJSON.data.hasOwnProperty(inputTarget)) {
                    $('#pmb-view-' + inputTarget).html((responseJSON.data[inputTarget]));
                }
            }
        }
        // close edition block
        var t = $form.data('profile-pmb-block-target');
        $(t).removeClass('toggled');
    }, null, null);
});