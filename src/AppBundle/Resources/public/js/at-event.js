/**
 * Created by Patman on 14/06/2016.
 */

$(document).ready(function () {

    autosize($('textarea'));

    $('.clockpicker').clockpicker();
    $('.datepicker').datetimepicker({
        format: "DD/MM/YYYY",
        locale: "fr",
        widgetPositioning: {
            horizontal: 'auto',
            vertical: 'bottom'
        }
    });
});

//Basic Example
$("[id^='event-data-table']").bootgrid({
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
        },
    },
});

/**
 * Génération des listes de prototype:
 *
 * - #module-list-container
 * --- #module-element-{typeOfModule}
 * -----  #module-proposal-list
 * -------  #module-proposal-element
 *
 */
var quoiPropositionsConteneur = $('#quoi-propositions-liste-champs');
var quoiPropositionsBoutonAjout = $('#add-quoi-proposition');

var quandPropositionsConteneur = $('#quand-propositions-liste-champs');
var quandPropositionsBoutonAjout = $('#add-quand-proposition');

var ouPropositionsConteneur = $('#ou-propositions-liste-champs');
var ouPropositionsBoutonAjout = $('#add-ou-proposition');

var qaqModulesConteneur = $('#modules-list-qui-apporte-quoi');
var qaqModulesBoutonAjout = $('#add-qui-apporte-quoi-module');

var qaqModulesCount = '{{ invitForm.invitationQaqs|length }}';

jQuery(document).ready(function () {
    processConteneur(quoiPropositionsConteneur, quoiPropositionsBoutonAjout);
    processConteneur(quandPropositionsConteneur, quandPropositionsBoutonAjout);
    processConteneur(ouPropositionsConteneur, ouPropositionsBoutonAjout);
    processConteneur(qaqModulesConteneur, qaqModulesBoutonAjout);
   /* {% set indexQaqModule = 0 %}
    {% for quiApporteQuoi in invitForm.invitationQaqs %}
    var qaqPropositionsConteneur = $('#qaq-propositions-liste-champs-{{ indexQaqModule }}');
    var qaqPropositionsBoutonAjout = $('#add-qaq-proposition-{{ indexQaqModule }}');
    processConteneur(qaqPropositionsConteneur, qaqPropositionsBoutonAjout);
    {% set indexQaqModule = indexQaqModule + 1 %}
    {% endfor %}*/


function processConteneur(conteneur, bouton) {
    conteneur.data('index', conteneur.find(':input').length);
    bouton.click(function (e) {
        e.preventDefault();
        // Get the data-prototype explained earlier
        var prototype = conteneur.data('prototype');
        var index = conteneur.data('index');
        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        if (prototype.indexOf('__module_name__') > -1) {
            var newForm = prototype.replace(/__module_name__/g, index);
        } else {
            var newForm = prototype.replace(/__name__/g, index);
        }
        // increase the index with one for the next item
        conteneur.data('index', index + 1);
        // Display the new form
        var newFormDiv = $('<div class="row"></div>').append(newForm);
        conteneur.append(newFormDiv);

        if (conteneur === qaqModulesConteneur) {
            var qaqPropositionsConteneur = $('#qaq-propositions-liste-champs-' + index);
            var qaqPropositionsBoutonAjout = $('#add-qaq-proposition-' + index);
            processConteneur(qaqPropositionsConteneur, qaqPropositionsBoutonAjout);
        }

        var input = newFormDiv.find('input:first');
        if (input != null && input.attr('id') != null) {
            if (input.attr('id').lastIndexOf('invitation_propositionQuandVotes_', 0) === 0) {
                //Initialisation des dates
                $('.clockpicker').clockpicker();
                /* newFormDiv.find('input:first').on("change", function () {
                 newFormDiv.find('input:last').focus();
                 newFormDiv.find('input:last').click();
                 })*/
                newFormDiv.find('input:first').focus();
            }

            if (input.attr('id').lastIndexOf('invitation_propositionOuVotes_', 0) === 0) {
                var inputId = /** @type {!HTMLInputElement} */(
                    document.getElementById(newFormDiv.find('input:first').attr('id')));
                var autocomplete = new google.maps.places.Autocomplete(inputId);
                // Creation du nouveau marker
                var marker = null;
                google.maps.event.addListener(autocomplete, 'place_changed', function onPlaceChanged() {
                    var infowindow = new google.maps.InfoWindow();
                    // On enregistre les points pour definir la zone visible de la map
                    if (bounds == null) {
                        bounds = new google.maps.LatLngBounds();
                    }

                    infowindow.close();
                    var place = autocomplete.getPlace();
                    if (!place.geometry) {
                        //window.alert("Autocomplete's returned place contains no geometry");
                        return;
                    }

                    /* Permet de s'assurer que la map est pas trop zoomée, a priori  le zoom du firbounds se fait en asynchrone,
                        donc il faut choper un event, et ne le fait que au premier passage*/
                    var zoomChangeBoundsListener = google.maps.event.addListener(mapArdGan, 'bounds_changed', function (event) {
                        if (mapArdGan.getZoom() > 15 && mapArdGan.initialZoom == true) {
                            // Change max/min zoom here
                            mapArdGan.setZoom(15);
                            mapArdGan.initialZoom = false;
                        }
                        google.maps.event.removeListener(zoomChangeBoundsListener);
                    });
                    mapArdGan.initialZoom = true;
                    bounds.extend(place.geometry.location)
                    mapArdGan.fitBounds(bounds);

                    marker = new google.maps.Marker({
                        map: mapArdGan,
                        position: place.geometry.location
                    });
                    google.maps.event.addListener(marker, 'click', function () {
                        infowindow.setContent(place.name);
                        infowindow.open(mapArdGan, this);
                    });

                    var inputHidden = /** @type {!HTMLInputElement} */(
                        document.getElementById(newFormDiv.find(':hidden:first').attr('id')));
                    inputHidden.value = place.place_id;
                });
            }

            newFormDiv.find('ul>li:first input[type=radio]').prop('checked', true);

            // add a delete link to the new form
            var btnSuppr = newFormDiv.find(":button");
            btnSuppr.on('click', function (e) {
                newFormDiv.remove();
                if (newFormDiv.find('input:first').attr('id').lastIndexOf('invitation_propositionOuVotes_', 0) === 0) {
                    marker.setMap(null);
                }
            });
            newFormDiv.find('input:first').focus();

            conteneur.find("div.row").each(function () {
                var btnSuppr = $(this).find(":button");
                btnSuppr.on('click', null, {objtoremove: $(this)}, function (event) {
                    event.data.objtoremove.remove();
                });
            })

        }

        $('.grid').masonry('reloadItems');
        $('.grid').masonry('layout');
    });
}


