/**
 * Created by Patman on 14/06/2016.
 */

$(document).ready(function () {

    initialiseYziplanMasonry();
    reorderCard();
    $('.grid').masonry('layout');

    window.onresize = function (event) {
        reorderCard();
        $('.grid').masonry('layout');
    };

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
            vertical: 'bottom'
        }
    });

    new Clipboard("#btn_url_event_public_invitation");
    new Clipboard('#btn_copy_invitation_url');

    /** Profile About Edit Toggle */
    if ($('[data-event-header-action]')[0]) {
        $('body').on('click', '[data-event-header-action]', function (e) {
            e.preventDefault();
            var d = $(this).data('event-header-action');
            var t = $(this).data('event-header-action-target');

            if (d === "edit") {
                $(t).toggleClass('toggled');
                $('.grid').masonry('layout');
            }
            if (d === "reset") {
                $(t).removeClass('toggled');
                $('.grid').masonry('layout');
            }

            initEventEditMap();
        });
    }

    // Bootgrid datatable pour les ExpenseModules
    /*$("[id^='event-data-table']").bootgrid({
     caseSensitive: false,
     css: {
     icon: 'zmdi icon',
     iconColumns: 'zmdi-view-module',
     iconDown: 'zmdi-expand-more',
     iconRefresh: 'zmdi-refresh',
     iconUp: 'zmdi-expand-less'
     },
     formatters: {
     "image": function (column, row) {
     var resultat = "";
     try {
     var jsonData = JSON.parse(row[column.id]);
     if (jsonData.hasOwnProperty("avatars")) {
     for (var i = 0; i < jsonData.avatars.length; i++) {
     var avatarSrc = "";
     var pseudo = "";
     // S'il y a un avatar
     if (jsonData.avatars[i].hasOwnProperty("avatarSrc")) {
     if (jsonData.avatars[i].hasOwnProperty("pseudo")) {
     pseudo = jsonData.avatars[i].pseudo
     }
     avatarSrc = jsonData.avatars[i].avatarSrc;
     resultat = resultat + "<img class='avatar-img' src='" + avatarSrc + "' alt='" + pseudo + "'  />";
     }
     // S'il n'y en a pas on indique la première lettre du pseudo
     else if (jsonData.avatars[i].hasOwnProperty("pseudo")) {
     pseudo = jsonData.avatars[i].pseudo;
     resultat = resultat + "<div class='avatar-char palette-Cyan-200 bg' name='" + pseudo + "'>" + pseudo.charAt(0).toUpperCase() + "</div>";
     }
     }
     }
     } catch (error) {
     }
     return resultat;
     }
     }
     });*/
});

/** Fonctions relatives à la page d'événement */

/**
 * Active l'autocomplétion (GooglePlace) pour les champs WHERE de formulaire et ajoute un listener pour mettre à jour un champ HIDDEN dédié
 */
function initPollProposalWhereElements(selectorFieldsPlaceName, selectorPlaceId) {
    var autocompletes = [];
    $(selectorFieldsPlaceName).each(function () {
        var placeNameInput = $(this);
        var autocomplete = new google.maps.places.Autocomplete(placeNameInput[0]);
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var inputHidden = placeNameInput.closest('.form-group').next(selectorPlaceId);
            inputHidden.val(autocomplete.getPlace().place_id);
        });
        autocompletes.push(autocomplete);

    });
    return autocompletes;
}

function reorderCard() {
    // Changement de position des div liste d'invité et
    // invitation en fonction de la largeur de l'ecran
    var iW = $(window).innerWidth();
    if (iW <= 768) {
        if (!$('#invitationCard').hasClass('grid-item')) {
            $('#invitationCard').addClass('grid-item');
            $('.grid').masonry('addItems',$('#invitationCard') );
        }
        $('#invitationCard').insertAfter('#eventModulesContainer');
    } else if (iW > 768 && iW < 1200) {
        if ($('#invitationCard').hasClass('grid-item')) {
            $('#invitationCard').removeClass('grid-item');
            $('.grid').masonry('destroy');
            initialiseYziplanMasonry();
        }
        $('#invitationCard').insertAfter('#profileCard');
    } else {
        if ($('#invitationCard').hasClass('grid-item')) {
            $('#invitationCard').removeClass('grid-item');
            $('.grid').masonry('destroy');
            initialiseYziplanMasonry();
        }
        $('#invitationCard').insertAfter('#guestListCard');
    }
}

function initialiseYziplanMasonry(){
    $('.grid').masonry({
        // options
        itemSelector: '.grid-item',
        columnWidth: '.grid-sizer',
        percentPosition: true
    });
}



