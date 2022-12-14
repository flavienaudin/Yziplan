/**
 * Created by Flavien on 01/07/2016.
 */

$(document).ready(function () {
    /** Common JQuery Selectors **/
    var $html = $('html');
    var $body = $('body');

    if (isMobile()) {
        $html.addClass('ismobile');
    }

    /** Global pre-loader */
    $('.at-global-preloader').hide();

    jsPlugginActivation();

    /* --------------------------------------------------------
     Scrollbar
     ----------------------------------------------------------*/
    if (!$html.hasClass('ismobile')) {
        //On Custom Class
        if ($('.c-overflow')[0]) {
            scrollBar('.c-overflow', 'minimal-dark', 'y');
        }
    }


    /* --------------------------------------------------------
     User Alerts
     ----------------------------------------------------------*/
    $body.on('click', '[data-user-alert]', function (e) {
        e.preventDefault();

        var u = $(this).data('user-alert');
        $('.' + u).tab('show');
    });


    /** Collapse Fix */
    initCollapse();


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
 * Detect mobile browser
 */
function isMobile() {
    if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)) {
        return true;
    }
}

/**
 * Active the plugin mCustomScrollBar
 */
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
/**
 * Fermeture des modals ouvertes lors du "retour arri??re"
 */
function closeModalOnReturn(modalsSelector) {
    $(modalsSelector).on('show.bs.modal', function () {
        var modal = this;
        window.location.hash = modal.id;
        window.onhashchange = function () {
            if (!location.hash) {
                $(modal).modal('hide');
            }
        };
    });

    $(modalsSelector).on('hide.bs.modal', function () {
        var hash = this.id;
        history.pushState('', document.title, window.location.pathname);
    });
}

/* --------------------------------------------------------
 Waves Animation
 ----------------------------------------------------------*/
function initBtnWavesAnimation() {
    Waves.attach('.btn:not(.btn-icon):not(.btn-float)');
    Waves.attach('.btn-icon, .btn-float', ['waves-circle', 'waves-float']);
    Waves.init();
}

/* --------------------------------------------------------
 Text Fields
 --------------------------------------------------------*/
function initTextFieldsFgLineFloat() {
    $body = $('body');

    //Add blue animated border and remove with condition when focus and blur
    if ($('.fg-line')[0]) {
        $body.on('focus', '.fg-line .form-control', function () {
            $(this).closest('.fg-line').addClass('fg-toggled');
        });

        $body.on('blur', '.form-control', function () {
            var p = $(this).closest('.form-group, .input-group');
            var i = p.find('.form-control').val();

            if (p.hasClass('fg-float')) {
                if (i.length === 0) {
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

            if (!i.length === 0) {
                $(this).closest('.fg-line').addClass('fg-toggled');
            }
        });
    }
}

/* --------------------------------------------------------
 Collapse Fix
 ----------------------------------------------------------*/
function initCollapse() {
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
}


/* --------------------------------------------------------
 Popover
 ----------------------------------------------------------*/
function initPopover() {
    var $popover = $('[data-toggle="popover"]');
    if ($popover[0]) {
        $popover.popover();
    }
}

/**
 * Active les pluggins JS/CSS apr??s une requ??te Ajax et au chargement d'une page
 *  - textarea autosize
 *  - clockpicker
 *  - datepicker
 *  - selectpicker
 *  - tooltip
 *  - masonry
 *  - Waves Effect
 *  - FgLine/FgFloat text fields
 *  - Popover
 *  - hidden-email rot13
 */
function jsPlugginActivation() {
    /** Autosize **/
    autosize($('textarea'));
    /** Auto Hight Textarea */
    var $autosizeElt = $('.auto-size');
    if ($autosizeElt[0]) {
        autosize($autosizeElt[0]);
    }

    //passage en readonly sur mobile et tablette pour eviter l'apparition du clavier.
    if (isMobile()) {
        $('.readonly-onmobile').attr('readonly', true);
    }

    $('.clockpicker').clockpicker();
    var locale_format = "dd/mm/yyyy";
    if (locale_js === 'en') {
        locale_format = "mm/dd/yyyy";
    }
    $('.ag-date-picker').datepicker({
        format: locale_format,
        language: locale_js,
        maxViewMode: 2,
        todayHighlight: true,
        autoclose: true,
        todayBtn: true,
        clearBtn: true
    });
    /*$('.ag-date-picker').datetimepicker({
     format: locale_format,
     locale: locale_js,
     showClear: true,
     ignoreReadonly: true,
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
     vertical: 'bottom'
     }
     });*/

    if (isMobile()) {
        $('.selectpicker:not([multiple="multiple"])').selectpicker('mobile');
        $('.selectpicker[multiple="multiple"]').selectpicker();
    } else {
        $('.selectpicker').selectpicker();
    }

    $('.toggle-tooltip, [data-toggle="tooltip"]').tooltip();

    var masonryGrid = $('.grid');
    if (masonryGrid[0] && Masonry.data(masonryGrid[0])) {
        // If a masonry is initialized
        masonryGrid.masonry('layout');
    }

    // Toggle pre-valued fg-float text feilds
    if ($('.fg-float')[0]) {
        $('.fg-float .form-control').each(function () {
            var i = $(this).val();
            if (i.length !== 0) {
                $(this).closest('.fg-line').addClass('fg-toggled');
            }
        });
    }

    /** Waves Animation */
    initBtnWavesAnimation();
    /** Text fields */
    initTextFieldsFgLineFloat();
    /** Popover */
    initPopover();

    /* --------------------------------------------------------
     Decode hidden email
     ----------------------------------------------------------*/
    $(".hidden-email").each(function () {
        var hrefEmail = $(this).attr("href");
        if (hrefEmail) {
            if (hrefEmail.indexOf("mailto:") === 0) {
                hrefEmail = hrefEmail.substr(7);
            }
            var decodedEmail = $.rot13(hrefEmail);
            $(this).attr("href", "mailto:" + decodedEmail);
        }
        $(this).rot13();
    });
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
// Attention au cas o?? une alerte de confirmation doit ??tre affich??e : e.preventDefault() doit ??tre appel?? avant l'appel ?? la fonction de requ??te Ajax

function ajaxRequest(target, data, event, doneCallback, failCallback, alwaysCallback, method) {
    if (event !== null) {
        event.preventDefault();
    }
    var preloader = $('.at-global-preloader');
    $(preloader).show();

    var urlTarget;
    if (typeof target === 'string') {
        urlTarget = target;
    } else {
        urlTarget = $(target).attr('href');
    }
    if (typeof method === "undefined") {
        method = 'post';
    }

    $.ajax({
        url: urlTarget,
        type: method,
        data: data
    }).done(function (responseJSON, textStatus, jqXHR) {
        if (typeof responseJSON !== 'undefined') {
            if (responseJSON.hasOwnProperty('htmlContents')) {
                treatHtmlContents(responseJSON['htmlContents']);
            }
            if (doneCallback) {
                doneCallback(responseJSON, textStatus, jqXHR);
            }
        } else {
            console.error('undefined response');
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        if (typeof jqXHR !== 'undefined') {
            if (failCallback) {
                failCallback(jqXHR, textStatus, errorThrown);
            }
        } else {
            console.error('undefined response');
        }
    }).always(function (responseDataOrJSON) {
        if (typeof responseDataOrJSON !== 'undefined') {
            if (responseDataOrJSON.hasOwnProperty('redirect')) {
                var waitTime = 0;
                if (responseDataOrJSON.hasOwnProperty('messages')) {
                    waitTime = 5000;
                }
                setTimeout(function () {
                    window.location.href = responseDataOrJSON['redirect'];
                }, waitTime);
            }
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
        } else {
            console.error('undefined response');
        }
    });
    return false;
}

function ajaxFormSubmission(form, event, doneCallback, failCallback, alwaysCallback, async) {
    if (typeof event !== 'undefined' && event !== null) {
        event.preventDefault();
    }
    var preloader = $('.at-global-preloader');
    $(preloader).show();

    // On pr??vient de la double soumission d'un formulaire
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

    var ajaxOptions = {
        url: $(form).attr('action'),
        type: $(form).attr('method')
    };

    if (typeof async === "boolean") {
        ajaxOptions.async = async;
    }

    if ($(form).attr("enctype") === "multipart/form-data") {
        ajaxOptions.data = new FormData($(form)[0]);
        ajaxOptions.contentType = false;
        ajaxOptions.processData = false;
    } else {
        ajaxOptions.data = $(form).serialize();
    }

    $.ajax(ajaxOptions)
        .done(function (responseJSON, textStatus, jqXHR) {
            if (typeof responseJSON !== 'undefined') {
                if (responseJSON.hasOwnProperty('htmlContents')) {
                    treatHtmlContents(responseJSON['htmlContents']);
                }
                if (doneCallback) {
                    doneCallback(responseJSON, textStatus, jqXHR);
                }
            } else {
                console.error('undefined response');
            }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            if (typeof jqXHR !== 'undefined') {
                var responseJSON = jqXHR['responseJSON'];
                if (typeof responseJSON !== 'undefined' && responseJSON.hasOwnProperty('htmlContents')) {
                    treatHtmlContents(responseJSON['htmlContents']);
                }
                if (failCallback) {
                    failCallback(jqXHR, textStatus, errorThrown);
                }
            } else {
                console.error('undefined response');
            }
        })
        .always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
            if (typeof dataOrJqXHR !== 'undefined') {
                if (alwaysCallback) {
                    alwaysCallback(dataOrJqXHR, textStatus, jqXHROrErrorThrown);
                }
                if (dataOrJqXHR.hasOwnProperty('responseJSON')) {
                    dataOrJqXHR = dataOrJqXHR['responseJSON'];
                }
                if (typeof dataOrJqXHR !== 'undefined' && dataOrJqXHR.hasOwnProperty('messages')) {
                    var messages = dataOrJqXHR['messages'];
                    for (var messageType in messages) {
                        if (messages.hasOwnProperty(messageType)) {
                            messages[messageType].forEach(function (mess) {
                                toastr[messageType](mess);
                            });
                        }
                    }
                }
            } else {
                console.error('undefined response');
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
 * Echappe les caract??res sp??ciaux d'un Selector pour JQuery
 * @param selector
 * @returns {string|XML|void}
 */
function escapeSelectorCharacters(selector) {
    return selector.replace(/(:|\.|\[|\]|,)/g, "\\$1");
}


function refuserToucheEntree(event) {
// Compatibilit?? IE / Firefox
    if (!event && window.event) {
        event = window.event;
    }
// IE
    if (event.keyCode === 13) {
        event.returnValue = false;
        event.cancelBubble = true;
    }
// DOM
    if (event.which === 13) {
        event.preventDefault();
        event.stopPropagation();
    }
}


/** Google Map related function */

/**
 * Active l'autocompl??tion (GooglePlace) pour les champs WHERE de formulaire et ajoute un listener pour mettre ?? jour un champ HIDDEN d??di??
 */
function initPollProposalWhereElements(selectorFieldsPlaceName, selectorPlaceId) {
    var autocompletes = [];
    $(selectorFieldsPlaceName).each(function () {
        var placeNameInput = $(this);
        var autocomplete = new google.maps.places.Autocomplete(placeNameInput[0]);
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var inputHidden = placeNameInput.closest('.form-group, .input-group').next(selectorPlaceId);
            inputHidden.val(autocomplete.getPlace().place_id);
        });
        autocompletes.push(autocomplete);

    });
    return autocompletes;
}
