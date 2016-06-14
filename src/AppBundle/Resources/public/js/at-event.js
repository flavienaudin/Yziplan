/**
 * Created by Patman on 14/06/2016.
 */

$(document).ready(function () {
    //Basic Example
    $("[id^='event-data-table']").bootgrid({
        css: {
            icon: 'zmdi icon',
            iconColumns: 'zmdi-view-module',
            iconDown: 'zmdi-expand-more',
            iconRefresh: 'zmdi-refresh',
            iconUp: 'zmdi-expand-less'
        },
        formatters: {
            "image": function (column, row) {
                var resultat = ""
                try {
                    var jsonData = JSON.parse(row[column.id]);
                    if (jsonData.hasOwnProperty("avatars")) {
                        for (var i = 0; i < jsonData.avatars.length; i++) {
                            var avatarSrc = "";
                            var pseudo = "";
                            if (jsonData.avatars[i].hasOwnProperty("avatarSrc")) {
                                avatarSrc = jsonData.avatars[i].avatarSrc
                            }
                            if (jsonData.avatars[i].hasOwnProperty("pseudo")) {
                                pseudo = jsonData.avatars[i].pseudo
                            }
                            resultat = resultat + "<img class='avatar-img' src='" + avatarSrc + "' alt='" + pseudo + "'  />";
                        }
                    }
                } catch (error) {
                }
                return resultat;
            },
        },
    });
});

