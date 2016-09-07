/**
 * Created by Flavien on 06/09/2016.
 */

/*------------------*/
/**- Selectize.JS -**/
/*------------------*/
var REGEX_EMAIL = '([a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@' +
    '(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';

/* Sélection des utilisateurs (User) */
/** Fonction d'affichage d'un item (option sélectionnée) */
function renderUserAsItem(item, escape) {
    return '<div>' +
        (item.text ? '<span class="name">' + escape(item.text) + '</span> ' : '') +
        (item.value ? '<span class="email">' + escape(item.value) + '</span>' : '') +
        '</div>';
}

/** Fonction d'affichage d'une option (élément de la liste) */
function renderUserAsOption(item, escape) {
    var label = item.text || item.value;
    var caption = item.text ? item.value : null;
    return '<div>' +
        '<span>' + escape(label) + '</span>' +
        (caption ? '<span class="caption">' + escape(caption) + '</span>' : '') +
        '</div>';
}

/** Fonction d'affichage de l'option de création d'un item */
function renderOptionCreateUser(item, escape) {
    return '<div class="create"><strong>' + escape(item.input) + '</strong>&hellip;</div>';
}

/** Fonction de validation de l'adresse email lors de l'ajout d'une @ pas dans la liste */
function createFilterUser(input) {
    var match, regex;

    // email@address.com
    regex = new RegExp('^' + REGEX_EMAIL + '$', 'i');
    match = input.match(regex);
    if (match) return !this.options.hasOwnProperty(match[0]);

    // name <email@address.com>
    regex = new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i');
    match = input.match(regex);
    if (match) return !this.options.hasOwnProperty(match[2]);

    return false;
}

/** Fonction de création d'un nouvel item */
function createUser(input) {
    if ((new RegExp('^' + REGEX_EMAIL + '$', 'i')).test(input)) {
        return {value: input};
    }
    var match = input.match(new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i'));
    if (match) {
        return {
            value: match[2],
            key: $.trim(match[1])
        };
    }
    return false;
}