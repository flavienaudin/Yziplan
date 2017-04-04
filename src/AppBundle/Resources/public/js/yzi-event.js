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

    $('textarea, .auto-size').on('autosize:resized', function () {
        $grid = $('.grid');
        if ($grid[0]) {
            $grid.masonry('layout');
        }
    });

    // Check if invitation is valid before opening the modal
    $('#eventEditModal').on('show.bs.modal', function (e) {
        if (!eventInvitationValid && askGuestName !== undefined) {
            askGuestName(e, function () {
                $('#eventEditModal').modal('show');
            }, null);
        }
    });

    // Check if invitation is valid before opening the modal
    $('#eventDuplicationSettingsModal').on('show.bs.modal', function (e) {
        if (!eventInvitationValid && askGuestName !== undefined) {
            askGuestName(e, function () {
                $('#eventDuplicationSettingsModal').modal('show');
            }, null);
        }
    });
});

/** Fonctions relatives à la page d'événement */
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
    var pollProposalId = params[3];

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
            newContent = '<span class="answer-thumb c-gray"><i class="zmdi zmdi-thumb-up-down"></i></span>';
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
                newContent = '<span class="answer-thumb c-gray"><i class="zmdi zmdi-plus zmdi-hc-2x"></i></span>';
            }
            document.getElementById("pollmodule_button_" + pollProposalId).innerHTML = newContent;
            LetterAvatar.transform();
        }, null,
        function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
            $('#pollresponse-preloader-' + pollProposalId).remove();
        });
}

function voteRankingAction(params) {
    var urlTarget = params[0];
    var e = params[1];
    var data = params[2];
    var pollProposalId = params[3];

    ajaxRequest(urlTarget, data, e, function (responseJSON, textStatus, jqXHR) {
        var icon = $('#pollmodule_button_' + pollProposalId);
        var animation = 'bounceIn';
        icon.addClass('animated ' + animation);
        setTimeout(function () {
            icon.removeClass(animation);
        }, 1000);
        // On met a jour le bouton de reponse et les boutons radio
        var newContent = '<span class="answer-thumb palette-Red-100 bg c-red strong">' + data.value + '<i class="zmdi zmdi-favorite"></i></span>'

        document.getElementById("pollmodule_button_" + pollProposalId).innerHTML = newContent;
        LetterAvatar.transform();
    }, null, function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
        $('.pollresponse-preloader-' + pollProposalId).remove();
    });
}

function addModuleAction(params) {
    var target = params[0];
    var e = params[1];
    ajaxRequest(target, null, e, function (responseJSON, textStatus, jqXHR) {
        var eventModulesContainer = $('#eventModulesContainer');
        $(document).scrollTop($(eventModulesContainer).children().last().offset().top - 100);
        $grid = $('.grid');
        if ($grid[0]) {
            $grid.masonry('layout');
        }
        LetterAvatar.transform();
    }, null, function () {
        $('.add-pollmodule-link').removeClass("disabled");
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

function submitMessageForm(params) {
    var form = params[0];
    var e = params[1];
    ajaxFormSubmission(form, e, function (responseJSON, textStatus, jqXHR) {
            $('#invitations_sendMessage_modal').modal('hide');
        }, function (jqXHR, textStatus, errorThrown) {
            $('#invitations_sendMessage_modal').modal('show');
        }, null
    );
}

/** fonction pour soumettre le formulaire d'édition d'un module */
function submitModuleEditionForm(params) {
    var form = params[0];
    var e = params[1];
    var moduleToken = params[2];
    ajaxFormSubmission(form, e, function (responseJSON, textStatus, jqXHR) {
        $("#moduleEdit_modal_" + moduleToken).modal('hide');
    }, null, function () {
        $grid = $('.grid');
        if ($grid[0]) {
            $grid.masonry('layout');
        }
    });
}
