/**
 * Created by Patman on 14/06/2016.
 */

$(document).ready(function () {
    initialiseYziplanMasonry();
    $('.grid').masonry('layout');

    window.onresize = function (event) {
        $('.grid').masonry('layout');
    };

    new Clipboard("#btn_url_event_public_invitation");
    new Clipboard('#btn_copy_invitation_url');

    $('textarea, .auto-size').on('autosize:resized', function () {
        $('.grid').masonry('layout');
    });

});

/** Fonctions relatives à la page d'événement */

// ScreenSize represente l'état précédent le redimensionnement (screen<768, 768<=screen<1200, 1200<=screen) pour ne rien faire si pas nécessaire
// Pour éviter le bug lors de l'affichage du clavier sous Android qui redimensionne la fenetre et recache le clavier
/* TODO Plus utilisée : à supprimer quand le layout sera validé
var screenSize = -1;
function reorderCard() {
    // Changement de position des div liste d'invité et
    // invitation en fonction de la largeur de l'ecran
    var iW = $(window).innerWidth();
    var invitationCard = $('#invitationCard');
    if (iW < 768 && screenSize != 0) {
        screenSize = 0;
        if (!invitationCard.hasClass('grid-item')) {
            invitationCard.addClass('grid-item');
            $('.grid').masonry('addItems', invitationCard);
        }
        invitationCard.insertAfter('#eventModulesContainer');
    } else if (iW >= 768 && iW < 1200 && screenSize != 1) {
        screenSize = 1;
        if (invitationCard.hasClass('grid-item')) {
            invitationCard.removeClass('grid-item');
            $('.grid').masonry('destroy');
            initialiseYziplanMasonry();
        }
        invitationCard.insertAfter('#profileCard');
    } else if (iW >= 1200 && screenSize != 2) {
        screenSize = 2;
        if (invitationCard.hasClass('grid-item')) {
            invitationCard.removeClass('grid-item');
            $('.grid').masonry('destroy');
            initialiseYziplanMasonry();
        }
        invitationCard.insertAfter('#guestListCard');
    }
}*/

function initialiseYziplanMasonry() {
    $('.grid').masonry({
        // options
        itemSelector: '.grid-item',
        columnWidth: '.grid-sizer',
        percentPosition: true
    });
}




