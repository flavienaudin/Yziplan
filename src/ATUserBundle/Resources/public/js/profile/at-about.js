/**
 * Created by Flavien on 07/06/2016.
 */

$(document).ready(function () {
    /** Profile TabPanel active */
    var anchor = getAnchor();
    var tabs = $('#profile-tabs');
    var tabToShow;
    if (anchor != null && (tabs.find('a[href="#' + anchor + '"]')[0])) {
        tabToShow = tabs.find('a[href="#' + anchor + '"]');
    } else {
        tabToShow = tabs.find('a:first');
    }
    tabToShow.tab('show');

    /** Profile About Edit Toggle */
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

$('form#userConnexionInformation, form#userPersonalInformation, form#userContactDetails, form#userBiography').on("submit", function (e) {
    var $form = $(this);
    ajaxFormSubmission(this, e, function (responseJSON, textStatus, jqXHR) {
        // update form's inputs
        if (responseJSON.data) {
            for (var inputTarget in responseJSON.data) {
                if (responseJSON.data.hasOwnProperty(inputTarget)) {
                    $('#pmb-view-' + inputTarget).html(responseJSON.data[inputTarget]);
                    /** Update information elsewhere in the page */
                    $('.user-' + inputTarget).each(function () {
                        $(this).html(responseJSON.data[inputTarget]);
                    });
                }
            }
        }
        // close edition block
        var t = $form.data('profile-pmb-block-target');
        $(t).removeClass('toggled');
    }, null, null);
});