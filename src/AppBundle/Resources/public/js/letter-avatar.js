/*
 * LetterAvatar
 *
 * Artur Heinze
 * Create Letter avatar based on Initials
 * based on https://gist.github.com/leecrossley/6027780
 *
 * Example of use : <img class="round" width="30" height="30" avatar="Andy">
 *
 * Version adapaté par Patrick
 */
(function (w, d) {

    function LetterAvatar(name, size, isUserConnected) {

        name = name || '';
        size = size || 150;

        var colours = [
                /* Couleurs originales
                 "#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e", "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50",
                 "#f1c40f", "#e67e22", "#e74c3c", "#ecf0f1", "#95a5a6", "#f39c12", "#d35400", "#c0392b", "#bdc3c7", "#7f8c8d"*/
                /* Flashy
                 "#ff1744", "#f50057", "#d500f9", "#651fff", "#3d5afe", "#2979ff", "#00b0ff", "#00e5ff", "#1de9b6", "#00e676",
                 "#76ff03", "#c6ff00", "#ffea00", "#ffc400", "#ff9100", "#ff3d00", "#d35400", "#c0392b", "#bdc3c7", "#7f8c8d"*/
                /* Pastel
                 "#e57373", "#f06292", "#ba68c8", "#9575cd", "#7986cb", "#64b5f6", "#4fc3f7", "#4dd0e1", "#4db6ac", "#81c784",
                 "#aed581", "#827717", "#fbc02d", "#ffa000", "#f57c00", "#ff8a65", "#a1887f", "#757575", "#90a4ae", "#000000"*/
                /* Orange */
                "#ffa000", "#ff8f00", "#ff6f00", "#f57c00", "#ef6c00", "#e65100", "#ff8a65", "#ff7043", "#ff5722", "#f4511e",
                "#ffa000", "#ff8f00", "#ff6f00", "#f57c00", "#ef6c00", "#e65100", "#ff8a65", "#ff7043", "#ff5722", "#f4511e"
            ],

            nameSplit = String(name).toUpperCase().split(' '),
            initials, charIndex, colourIndex, canvas, context, dataURI;


        if (nameSplit.length == 1) {
            initials = nameSplit[0] ? nameSplit[0].charAt(0) : '?';
        } else {
            initials = nameSplit[0].charAt(0) + nameSplit[1].charAt(0);
        }

        if (w.devicePixelRatio) {
            size = (size * w.devicePixelRatio);
        }

        charIndex = (initials == '?' ? 72 : initials.charCodeAt(0)) - 64;
        colourIndex = charIndex % 20;
        canvas = d.createElement('canvas');
        canvas.width = size;
        canvas.height = size;
        context = canvas.getContext("2d");

        if (!isUserConnected) {
            context.fillStyle = colours[colourIndex];
        } else { // Dans le cas ou on est sur l'avatar de l'utilisateur
            context.fillStyle = "#d500f9";
        }

        context.fillRect(0, 0, canvas.width, canvas.height);
        context.font = Math.round(canvas.width / 2) + "px Arial";
        context.textAlign = "center";
        context.fillStyle = "#FFF";
        context.fillText(initials, size / 2, size / 1.5);

        dataURI = canvas.toDataURL();
        canvas = null;

        return dataURI;
    }

    LetterAvatar.transform = function () {
        Array.prototype.forEach.call(d.querySelectorAll('img[avatar]'), function (img, name) {
            name = img.getAttribute('avatar');
            img.src = LetterAvatar(name, img.getAttribute('width'), img.hasAttribute('userAvatar'));
            if (name != '' && name != null) {
                img.removeAttribute('avatar');
            }
            img.setAttribute('alt', name);
        });
    };

    LetterAvatar.setConnectedUserName = function (userName) {
        Array.prototype.forEach.call(d.querySelectorAll('img[avatar][userAvatar]'), function (img) {
            img.setAttribute('avatar', userName);
        });
    };

    // AMD support
    if (typeof define === 'function' && define.amd) {

        define(function () {
            return LetterAvatar;
        });

        // CommonJS and Node.js module support.
    } else if (typeof exports !== 'undefined') {

        // Support Node.js specific `module.exports` (which can be a function)
        if (typeof module != 'undefined' && module.exports) {
            exports = module.exports = LetterAvatar;
        }

        // But always support CommonJS module 1.1.1 spec (`exports` cannot be a function)
        exports.LetterAvatar = LetterAvatar;

    } else {

        window.LetterAvatar = LetterAvatar;

        d.addEventListener('DOMContentLoaded', function (event) {
            LetterAvatar.transform();
        });
    }

})(window, document);