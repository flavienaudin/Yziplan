/**
 * Created by Patman on 14/06/2016.
 */

$(document).ready(function () {
    initialiseYziplanMasonry();
    $grid = $('.grid');
    if ($grid[0]) {
        $grid.masonry('layout');
    }

    window.onresize = function (event) {
        $grid = $('.grid');
        if ($grid[0]) {
            $grid.masonry('layout');
        }
    };

    new Clipboard("#btn_url_event_public_invitation");

    $('textarea, .auto-size').on('autosize:resized', function () {
        $grid = $('.grid');
        if ($grid[0]) {
            $grid.masonry('layout');
        }
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

/**********************************************************************************************************************
 *                                   Fonction de vote
 ************************************************************************************************************************/
function voteYesNoAction(params) {
    var urlTarget = params[0];
    var e = params[1];
    var data = params[2];

    var pollProposalId = params[4];

    ajaxRequest(urlTarget, data, e, function (responseJSON, textStatus, jqXHR) {
        var animation = 'bounceIn';
        var icon = $('#pollmodule_button_' + pollProposalId);
        icon.addClass('animated ' + animation);
        setTimeout(function () {
            icon.removeClass(animation);
        }, 1000);
        // On met a jour le bouton de reponse et les boutons radio
        var newContent = null;
        if (data['value'] == 'pollproposalresponse.yes') {
            newContent = '<span class="answer-thumb palette-Light-Green-100 bg c-lightgreen strong"><i class="zmdi zmdi-thumb-up"></i></span>';
            $('#pollProposalReponse_' + pollProposalId + '_yes').toggleClass('active');
            $('#pollProposalReponse_' + pollProposalId + '_maybe').removeClass('active');
            $('#pollProposalReponse_' + pollProposalId + '_no').removeClass('active');
        } else if (data['value'] == 'pollproposalresponse.maybe') {
            newContent = '<span class="answer-thumb palette-Amber-100 bg c-amber strong"><i class="zmdi zmdi-thumb-up-down"></i></span>';
            $('#pollProposalReponse_' + pollProposalId + '_maybe').toggleClass('active');
            $('#pollProposalReponse_' + pollProposalId + '_yes').removeClass('active');
            $('#pollProposalReponse_' + pollProposalId + '_no').removeClass('active');
        } else if (data['value'] == 'pollproposalresponse.no') {
            newContent = ' <span class="answer-thumb palette-Red-50 bg c-red strong"><i class="zmdi zmdi-thumb-down"></i></span>';
            $('#pollProposalReponse_' + pollProposalId + '_no').toggleClass('active');
            $('#pollProposalReponse_' + pollProposalId + '_yes').removeClass('active');
            $('#pollProposalReponse_' + pollProposalId + '_maybe').removeClass('active');
        } else {
            newContent = '<span class="answer-thumb text palette-White"><i class="zmdi zmdi-thumb-up-down"></i></span>';
        }
        document.getElementById("pollmodule_button_" + pollProposalId).innerHTML = newContent;
        LetterAvatar.transform();
    }, null, function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
        $('#pollresponse-preloader-' + pollProposalId).remove();
    });
}

function voteAmountAction(params) {
    var urlTarget = params[0];
    var e = params[1];
    var data = params[2];
    var pollProposalId = params[3];

    ajaxRequest(urlTarget, data, e, function (responseJSON, textStatus, jqXHR) {
            var newContent = null;
            if (data['value'] > 0) {
                newContent = '<span class="answer-thumb palette-Light-Green-100 bg strong"><span class="palette-Black text">' + data['value'] + '</span></span>';
            } else {
                newContent = '<span class="answer-thumb text palette-White"><i class="zmdi zmdi-plus zmdi-hc-2x"></i></span>';
            }
            document.getElementById("pollmodule_button_" + pollProposalId).innerHTML = newContent;
            LetterAvatar.transform();
        }, null,
        function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
            $('#pollresponse-preloader-' + pollProposalId).remove();
        });
}

function submitAddPollProposalForm(params) {
    var form = params[0];
    var e = params[1];
    var moduleToken = params[2];
    var ppModalPrefix = params[3];
    var edition = params[4];
    ajaxFormSubmission(form, e, function (responseJSON, textStatus, jqXHR) {
            if (!edition) {
                $('#pollmodule-table-' + moduleToken + ' > tbody > tr:nth-last-child(1)').before(responseJSON['data']);
                $('#pollModuleDisplayAllResult_' + moduleToken + '_button').show();
            }
            $('#' + ppModalPrefix + '_modal_id').modal('hide');
        }, function (jqXHR, textStatus, errorThrown) {
            $('#' + ppModalPrefix + '_modal_id').modal('show');
        }, function () {
            $grid = $('.grid');
            if ($grid[0]) {
                $grid.masonry('layout');
            }
        }
    );
}


