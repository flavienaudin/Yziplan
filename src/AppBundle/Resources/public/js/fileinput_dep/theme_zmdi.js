/*!
 * bootstrap-fileinput v4.3.5
 * http://plugins.krajee.com/file-input
 *
 * Material Design icon theme configuration for bootstrap-fileinput. Requires "Material Design Iconic Font" assets to be loaded.
 *
 * Author: Flavien Audin
 *
 * Licensed under the BSD 3-Clause
 * https://github.com/kartik-v/bootstrap-fileinput/blob/master/LICENSE.md
 */
(function ($) {
    "use strict";

    $.fn.fileinputThemes.zmdi = {
        fileActionSettings: {
            removeIcon: '<i class="zmdi zmdi-delete text-danger"></i>',
            uploadIcon: '<i class="zmdi zmdi-upload text-info"></i>',
            zoomIcon: '<i class="zmdi zmdi-search"></i>',
            dragIcon: '<i class="zmdi zmdi-menu"></i>',
            indicatorNew: '<i class="zmdi zmdi-alert-polygon text-warning"></i>',
            indicatorSuccess: '<i class="zmdi zmdi-check-circle text-success"></i>',
            indicatorError: '<i class="zmdi zmdi-alert-circle text-danger"></i>',
            indicatorLoading: '<i class="zmdi zmdi-thumb-up text-muted"></i>'
        },
        layoutTemplates: {
            fileIcon: '<i class="zmdi zmdi-file kv-caption-icon"></i> '
        },
        previewZoomButtonIcons: {
            prev: '<i class="zmdi zmdi-caret-left zmdi-hc-lg"></i>',
            next: '<i class="zmdi zmdi-caret-right zmdi-hc-lg"></i>',
            toggleheader: '<i class="zmdi zmdi-window-minimize"></i>',
            fullscreen: '<i class="zmdi zmdi-fullscreen"></i>',
            borderless: '<i class="zmdi zmdi-fullscreen-alt"></i>',
            close: '<i class="zmdi zmdi-close"></i>'
        },
        previewFileIcon: '<i class="zmdi zmdi-file"></i>',
        browseIcon: '<i class="zmdi zmdi-folder"></i>',
        removeIcon: '<i class="zmdi zmdi-delete"></i>',
        cancelIcon: '<i class="zmdi zmdi-block"></i>',
        uploadIcon: '<i class="zmdi zmdi-upload"></i>',
        msgValidationErrorIcon: '<i class="zmdi zmdi-alert-circle"></i> '
    };
})(window.jQuery);
