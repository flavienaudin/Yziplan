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

