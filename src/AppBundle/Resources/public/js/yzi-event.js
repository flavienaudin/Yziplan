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

    new Clipboard("#btn_url_event_public_invitation");
    new Clipboard('#btn_copy_invitation_url');

    /** Event Edit Toggle */
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

    $('textarea, .auto-size').on('autosize:resized', function(){
        $('.grid').masonry('layout');
    });
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


