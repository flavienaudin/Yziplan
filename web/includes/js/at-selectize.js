/**
 * Created by Flavien on 06/09/2016.
 */

/*------------------*/
/**- Selectize.JS -**/
/*------------------*/
var REGEX_EMAIL = '([a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@' +
    '(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';

/* Common */
/** Fonction d'affichage de l'option de création d'un item */
function renderOptionCreate(item, escape) {
    return '<div class="create"><strong>' + escape(item.input) + '</strong>&hellip;</div>';
}

/** Fonction de validation de l'adresse email lors de l'ajout d'une @ pas dans la liste */
function createFilterEmail(input) {
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

/* Sélection des contacts pour les ajouter aux contacts d'un utilisateur */
/** Fonction d'affichage d'un item (option sélectionnée) */
function renderContactAsItem(item, escape) {
    var avatar = '<i class="zmdi zmdi-hc-fw zmdi-account-circle"></i>';
    if (item.avatarPath) {
        avatar = '<img src="' + item.avatarPath + '" alt="Avatar de ' + item.nom +
            '" class="img-responsive img-circle" >';
    }
    return '<div><div class="avatar-img-container-xs">' + avatar + '</div> ' +
        '<span class="name">' + escape(item.nom) + '</span></div>';
}

/** Fonction d'affichage d'une option (élément de la liste) */
function renderContactAsOption(item, escape) {
    var label = item.nom;
    var avatar = '<i class="fa fa-fw fa-user"></i>';
    if (item.avatarPath) {
        avatar = '<img src="' + item.avatarPath + '" alt = "Avatar de ' + item.nom +
            '" class="img-responsive img-circle">';
    }
    return '<div><div class="avatar-img-container-xs">' + avatar + '</div> ' +
        '<span>' + escape(label) + '</span></div>';
}

/** Fonction de création d'un nouvel item (Contact) */
function createContact(input, callback) {
    if ((new RegExp('^' + REGEX_EMAIL + '$', 'i')).test(input)) {
        var data = {
            nom: input,
            email: input,
            avatarPath: null,
            id: input
        };
        callback(data);
        return data;
    }
    var match = input.match(new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i'));
    if (match) {
        var data = {
            nom: $.trim(match[1]),
            email: match[2],
            avatarPath: null,
            id: match[2]
        };
        callback(data);
        return data;
    }
    return false;
}