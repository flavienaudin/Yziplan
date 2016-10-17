/**
 * Created by Flavien on 01/07/2016.
 */


$(document).ready(function () {
    /** Global pre-loader */
    $('.at-global-preloader').hide();

    jsPlugginActivation();

    /** Common JQuery Selectors **/
    var $html = $('html');
    var $body = $('body');

    /* --------------------------------------------------------
     Scrollbar
     ----------------------------------------------------------*/
    function scrollBar(selector, theme, mousewheelaxis) {
        $(selector).mCustomScrollbar({
            theme: theme,
            scrollInertia: 100,
            axis: 'yx',
            mouseWheel: {
                enable: true,
                axis: mousewheelaxis,
                preventDefault: true
            }
        });
    }

    if (!$html.hasClass('ismobile')) {
        //On Custom Class
        if ($('.c-overflow')[0]) {
            scrollBar('.c-overflow', 'minimal-dark', 'y');
        }
    }

    /* --------------------------------------------------------
     Top Search
     ----------------------------------------------------------*/
    /* Bring search reset icon when focused */
    $body.on('focus', '.hs-input', function () {
        $('.h-search').addClass('focused');
    });

    /* Take off reset icon if input length is 0, when blurred */
    $body.on('blur', '.hs-input', function () {
        var x = $(this).val();

        if (!x.length > 0) {
            $('.h-search').removeClass('focused');
        }
    });


    /* --------------------------------------------------------
     User Alerts
     ----------------------------------------------------------*/
    $body.on('click', '[data-user-alert]', function (e) {
        e.preventDefault();

        var u = $(this).data('user-alert');
        $('.' + u).tab('show');

    });

    /* --------------------------------------------------------
     Text Feild
     ----------------------------------------------------------*/

    //Add blue animated border and remove with condition when focus and blur
    if ($('.fg-line')[0]) {
        $body.on('focus', '.fg-line .form-control', function () {
            $(this).closest('.fg-line').addClass('fg-toggled');
        });

        $body.on('blur', '.form-control', function () {
            var p = $(this).closest('.form-group, .input-group');
            var i = p.find('.form-control').val();

            if (p.hasClass('fg-float')) {
                if (i.length == 0) {
                    $(this).closest('.fg-line').removeClass('fg-toggled');
                }
            }
            else {
                $(this).closest('.fg-line').removeClass('fg-toggled');
            }
        });
    }

    //Add blue border for pre-valued fg-flot text feilds
    if ($('.fg-float')[0]) {
        $('.fg-float .form-control').each(function () {
            var i = $(this).val();
            if (i.length != 0) {
                $(this).closest('.fg-line').addClass('fg-toggled');
            }

        });
    }

    /* --------------------------------------------------------
     Waves Animation
     ----------------------------------------------------------*/
    (function () {
        Waves.attach('.btn:not(.btn-icon):not(.btn-float)');
        Waves.attach('.btn-icon, .btn-float', ['waves-circle', 'waves-float']);
        Waves.init();
    })();


    /* --------------------------------------------------------
     Collapse Fix
     ----------------------------------------------------------*/
    var $collapse = $('.collapse');
    if ($collapse[0]) {
        //Add active class for opened items
        $collapse.on('show.bs.collapse', function () {
            $(this).closest('.panel').find('.panel-heading').addClass('active');
        });

        $collapse.on('hide.bs.collapse', function () {
            $(this).closest('.panel').find('.panel-heading').removeClass('active');
        });

        //Add active class for pre opened items
        $('.collapse.in').each(function () {
            $(this).closest('.panel').find('.panel-heading').addClass('active');
        });
    }

    /* --------------------------------------------------------
     Tooltips
     ----------------------------------------------------------*/
    var $tooltip = $('[data-toggle="tooltip"]');
    if ($tooltip[0]) {
        $tooltip.tooltip();
    }

    /* --------------------------------------------------------
     Popover
     ----------------------------------------------------------*/
    var $popover = $('[data-toggle="popover"]');
    if ($popover[0]) {
        $popover.popover();
    }

    /* --------------------------------------------------------
     IE 9 Placeholder
     ----------------------------------------------------------*/
    if ($html.hasClass('ie9')) {
        $('input, textarea').placeholder({
            customClass: 'ie9-placeholder'
        });
    }
});


/**
 * Active les pluggins JS/CSS après une requête Ajax et au chargement d'une page
 *  - textarea autosize
 *  - clockpicker
 *  - datepicker
 */
function jsPlugginActivation() {
    /** Autosize **/
    autosize($('textarea'));
    /** Auto Hight Textarea */
    var $autosizeElt = $('.auto-size');
    if ($autosizeElt[0]) {
        autosize($autosizeElt[0]);
    }

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
    $('.selectpicker').selectpicker();
    $('.toggle-tooltip, [data-toggle="tooltip"]').tooltip();
}

/**
 * Get URL get parameter by its name
 * @param name Parameter's name
 * @param url (string|null) Optionnal URL to search in, if undefined then current URL is used
 * @returns {*}
 */
function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

/**
 * Get anchor presents in the URL
 * @param url (string|null) Optionnal URL to search in, if undefined then current URL is used
 * @returns {*}
 */
function getAnchor(url) {
    if (!url) url = window.location.href;
    var anchor = null;
    var idx = url.indexOf("#");
    if (idx > 0) {
        anchor = url.substring(idx + 1);
    }
    return anchor;
}

/*----------------*/
/** Ajax Request **/
/*----------------*/
// TODO remove for PROD
// Attention au cas où une alerte de confirmation est demandé au préalable (e.preventDefault déclencé avant l'appel à la function)
var disabledAjax = false;
function ajaxRequest(target, data, event, doneCallback, failCallback, alwaysCallback) {
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
        type: 'post',
        data: data
    }).done(function (responseJSON, textStatus, jqXHR) {
        if (responseJSON.hasOwnProperty('htmlContents')) {
            treatHtmlContents(responseJSON['htmlContents']);
        }
        if (doneCallback) {
            doneCallback(responseJSON, textStatus, jqXHR);
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        if (failCallback) {
            failCallback(jqXHR, textStatus, errorThrown);
        }
    }).always(function (responseDataOrJSON) {
        if (alwaysCallback) {
            alwaysCallback(responseDataOrJSON);
        }
        if (responseDataOrJSON.hasOwnProperty('responseJSON')) {
            responseDataOrJSON = responseDataOrJSON['responseJSON'];
        }
        if (responseDataOrJSON.hasOwnProperty('messages')) {
            var messages = responseDataOrJSON['messages'];
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
        if (responseJSON.hasOwnProperty('htmlContents')) {
            treatHtmlContents(responseJSON['htmlContents']);
        }
        if (doneCallback) {
            doneCallback(responseJSON, textStatus, jqXHR);
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        var responseJSON = jqXHR.responseJSON;
        if (responseJSON != undefined && responseJSON.formErrors) {
            var formErrors = responseJSON.formErrors;
            for (var fieldErrorName in formErrors) {
                if (formErrors.hasOwnProperty(fieldErrorName)) {
                    var inputField = $('input[name*=' + escapeSelectorCharacters(fieldErrorName) + ']');
                    inputField.closest('.form-group, .input-group').addClass("has-error");
                    inputField.after('<small class="help-block">' + responseJSON.formErrors[fieldErrorName] + '</small>')
                }
            }
        }
        if (failCallback) {
            failCallback(jqXHR, textStatus, errorThrown);
        }
    }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
        if (alwaysCallback) {
            alwaysCallback(dataOrJqXHR, textStatus, jqXHROrErrorThrown);
        }
        if (dataOrJqXHR.hasOwnProperty('responseJSON')) {
            dataOrJqXHR = dataOrJqXHR['responseJSON'];
        }
        if (dataOrJqXHR.hasOwnProperty('messages')) {
            var messages = dataOrJqXHR['messages'];
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
 * Update or Append HTML content
 * @param htmlContents
 */
function treatHtmlContents(htmlContents) {
    for (var htmlContentAction in htmlContents) {
        if (htmlContents.hasOwnProperty(htmlContentAction)) {
            for (var htmlId in htmlContents[htmlContentAction]) {
                if (htmlContents[htmlContentAction].hasOwnProperty(htmlId)) {
                    var hContent = htmlContents[htmlContentAction][htmlId];
                    var eltParent = $(htmlId);
                    if (eltParent[0]) {
                        eltParent[htmlContentAction](hContent);
                    }
                }
            }
        }
    }
    jsPlugginActivation();
}

/**
 * Echappe les caractères spéciaux d'un Selector pour JQuery
 * @param selector
 * @returns {string|XML|void}
 */
function escapeSelectorCharacters(selector) {
    return selector.replace(/(:|\.|\[|\]|,)/g, "\\$1");
}