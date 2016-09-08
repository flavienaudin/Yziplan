/**
 * Created by Flavien on 01/07/2016.
 */


$(document).ready(function () {
    /** Global pre-loader */
    $('.at-global-preloader').hide();
});


/*----------------*/
/** Ajax Request **/
/*----------------*/
// TODO remove for PROD
// Attention au cas où une alerte de confirmation est demandé au préalable (e.preventDefault déclencé avant l'appel à la function)
var disabledAjax = false;
function ajaxRequest(target, event, doneCallback, failCallback, alwaysCallback) {
    if (disabledAjax) {
        return true;
    }
    if (event != null) {
        event.preventDefault();
    }
    var preloader = $('.at-global-preloader');
    $(preloader).show();

    $.ajax({
        url: $(target).attr("href"),
        type: 'post'
    }).done(function (responseJSON, textStatus, jqXHR) {
        if (doneCallback) {
            doneCallback(responseJSON, textStatus, jqXHR);
        }
        if (responseJSON.htmlContent) {
            jsPlugginActivation();
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        if (failCallback) {
            failCallback(jqXHR, textStatus, errorThrown);
        }
    }).always(function (responseDataOrJSON) {
        if (alwaysCallback) {
            alwaysCallback(responseDataOrJSON);
        }
        if (responseDataOrJSON.responseJSON) {
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
        $(preloader).hide();
    });
    return false;
}

function ajaxFormSubmission(form, event, doneCallback, failCallback, alwaysCallback) {
    if (disabledAjax) {
        return true;
    }
    if (event != null) {
        event.preventDefault();
    }
    var preloader = $('.at-global-preloader');
    $(preloader).show();

    $(form).find('[type=submit]').each(function () {
        $(this).on('click', function (e) {
            e.preventDefault();
        });
        $(this).prop("disabled");
        $(this).addClass("disabled");
        if ($(this).data('loading-text')) {
            $(this).html($(this).data('loading-text'));
        }
    });

    $('.has-error').each(function () {
        $(this).find("small.help-block").remove();
        $(this).removeClass("has-error");
    });

    $.ajax({
        url: $(form).attr('action'),
        type: $(form).attr('method'),
        data: $(form).serialize()
    }).done(function (responseJSON, textStatus, jqXHR) {
        if (doneCallback) {
            doneCallback(responseJSON, textStatus, jqXHR);
        }
        if (responseJSON.htmlContent) {
            jsPlugginActivation();
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        var responseJSON = jqXHR.responseJSON;
        if (responseJSON != undefined && responseJSON.formErrors) {
            var formErrors = responseJSON.formErrors;
            for (var fieldErrorName in formErrors) {
                if (formErrors.hasOwnProperty(fieldErrorName)) {
                    var inputField = $('input[name*=' + escapeSelectorCharacters(fieldErrorName) + ']');
                    inputField.parent().addClass("has-error");
                    inputField.after('<small class="help-block">' + responseJSON.formErrors[fieldErrorName] + '</small>')
                }
            }
        }
        if (failCallback) {
            failCallback(jqXHR, textStatus, errorThrown);
        }
    }).always(function (responseDataOrJSON) {
        if (alwaysCallback) {
            alwaysCallback(responseDataOrJSON);
        }
        if (responseDataOrJSON.responseJSON) {
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
        $(preloader).hide();
        $(form).find('[type=submit]').each(function () {
            $(this).off('click');
            $(this).removeProp("disabled");
            $(this).removeClass("disabled");
            if ($(this).data('original-text')) {
                $(this).html($(this).data('original-text'));
            }
        });
    });
}

/**
 * Active les pluggins JS/CSS après une requête Ajax
 *  - textarea autosize
 *  - clockpicker
 *  - datepicker
 */
function jsPlugginActivation() {
    autosize($('textarea'));
    $('.clockpicker').clockpicker();
    $('.datepicker').datetimepicker({
        format: "DD/MM/YYYY",
        locale: "fr",
        showClear: true,
        icons: {
            time: 'zmdi zmdi-time',
            date: 'zmdi zmdi-calendar',
            up: 'zmdi zmdi-chevron-up',
            down: 'zmdi zmdi-chevron-down',
            previous: 'zmdi zmdi-chevron-left',
            next: 'zmdi zmdi-chevron-right',
            today: 'zmdi zmdi-gps-dot',
            clear: 'zmdi zmdi-delete zmdi-hc-lg',
            close: 'zmdi zmdi-close-circle-o zmdi-hc-lg'
        },
        widgetPositioning: {
            horizontal: 'auto',
            vertical: 'auto'
        }
    });
}

/**
 * Echappe les caractères spéciaux d'un Selector pour JQuery
 * @param selector
 * @returns {string|XML|void}
 */
function escapeSelectorCharacters(selector ) {
    return selector.replace( /(:|\.|\[|\]|,)/g, "\\$1" );
}