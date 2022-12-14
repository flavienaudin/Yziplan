/**
 * Created by Flavien on 16/12/2016.
 * This file is based on the "comment.js" file of the FOSCommentBundle package.
 * It is adapted to Yziplan specifications and methods
 */
/**
 * This file is part of the FOSCommentBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * To use this reference javascript, you must also have jQuery installed. If
 * you want to embed comments cross-domain, then easyXDM CORS is also required.
 *
 * @todo: expand this explanation (also in the docs)
 *
 * Then a comment thread can be embedded on any page:
 *
 * <div id="fos_comment_thread">#comments</div>
 * <script type="text/javascript">
 *     // Set the thread_id if you want comments to be loaded via ajax (url to thread comments api)
 *     var fos_comment_thread_id = 'a_unique_identifier_for_the_thread';
 *     var fos_comment_thread_api_base_url = 'http://example.org/api/threads';
 *
 *     // Optionally set the cors url if you want cross-domain AJAX (also needs easyXDM)
 *     var fos_comment_remote_cors_url = 'http://example.org/cors/index.html';
 *
 *     // Optionally set a custom callback function to update the comment count elements
 *     var fos_comment_thread_comment_count_callback = function(elem, threadObject){}
 *
 *     // Optionally set a different element than div#fos_comment_thread as container
 *     var fos_comment_thread_container = $('#other_element');
 *
 * (function() {
 *     var fos_comment_script = document.createElement('script');
 *     fos_comment_script.async = true;
 *     fos_comment_script.src = 'http://example.org/path/to/this/file.js';
 *     fos_comment_script.type = 'text/javascript';
 *
 *     (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(fos_comment_script);
 * })();
 * </script>
 */

/**
 * Fucntion to initialise the object FOS_COMMENT and its methods
 * @param window
 * @param $
 * @param easyXDM
 */
(function (window, $, easyXDM) {
    "use strict";
    var FOS_COMMENT = {
        /**
         * Shorcut post method.
         *
         * @param target string url The url of the page to post.
         * @param data object data The data to be posted.
         * @param e JS event
         * @param doneCallback function Optional callback function to use in case of succes.
         * @param failCallback function Optional callback function to use in case of error.
         * @param alwaysCallback function Optional callback function to use after the call or success or error.
         */
        post: ajaxRequest,

        /**
         * Shorcut post method.
         *
         * @param target string url The url of the page to post.
         * @param data object data The data to be posted.
         * @param e JS event
         * @param doneCallback function Optional callback function to use in case of succes.
         * @param failCallback function Optional callback function to use in case of error.
         * @param alwaysCallback function Optional callback function to use after the call or success or error.
         * @param method
         */
        get: ajaxRequest,

        /**
         * Gets the comments of a thread and places them in the thread holder.
         *
         * @param thread_container DOMElement The container where thread is displayed. Must have the attribut "data-threadId"
         * @param permalink string Optional url for the thread. Defaults to current location.
         */
        getThreadComments: function (thread_container, permalink) {
            var identifier = thread_container.data('threadId');

            if ('undefined' == typeof identifier) {
                return;
            }

            var event = jQuery.Event('fos_comment_before_load_thread');

            event.identifier = identifier;
            event.params = {
                permalink: encodeURIComponent(permalink || window.location.href)
            };

            FOS_COMMENT.thread_container.trigger(event);
            FOS_COMMENT.get(
                FOS_COMMENT.base_url + '/' + encodeURIComponent(event.identifier) + '/comments',
                event.params,
                null,
                // success
                function (data) {
                    thread_container.html(data);
                    thread_container.attr('data-thread', event.identifier);
                    thread_container.trigger('fos_comment_load_thread', event.identifier);
                },
                'get'
            );
        },

        /**
         * Initialize the event listeners.
         */
        initializeListeners: function () {
            // Remove previously recorded events
            FOS_COMMENT.thread_container.off('submit', 'form.fos_comment_comment_new_form', commentNewFormSubmit);
            FOS_COMMENT.thread_container.off('click', '.fos_comment_comment_reply_show_form', commentReplyShowFormClick);
            FOS_COMMENT.thread_container.off('click', '.fos_comment_comment_reply_cancel', commentReplyCancelClick);
            FOS_COMMENT.thread_container.off('click', '.fos_comment_comment_edit_show_form', commentEditShowFormClick);
            FOS_COMMENT.thread_container.off('submit', 'form.fos_comment_comment_edit_form', commentEditFormSubmit);
            FOS_COMMENT.thread_container.off('click', '.fos_comment_comment_edit_cancel', commentEditCancelClick);
            FOS_COMMENT.thread_container.off('click', '.fos_comment_comment_vote', commentVoteClick);
            FOS_COMMENT.thread_container.off('click', '.fos_comment_comment_remove', commentRemoveClick);
            FOS_COMMENT.thread_container.off('click', '.fos_comment_thread_commentable_action', threadCommentableActionClick);


            FOS_COMMENT.thread_container.on('submit', 'form.fos_comment_comment_new_form', commentNewFormSubmit);

            FOS_COMMENT.thread_container.on('click', '.fos_comment_comment_reply_show_form', commentReplyShowFormClick);

            FOS_COMMENT.thread_container.on('click', '.fos_comment_comment_reply_cancel', commentReplyCancelClick);

            FOS_COMMENT.thread_container.on('click', '.fos_comment_comment_edit_show_form', commentEditShowFormClick);

            FOS_COMMENT.thread_container.on('submit', 'form.fos_comment_comment_edit_form', commentEditFormSubmit);

            FOS_COMMENT.thread_container.on('click', '.fos_comment_comment_edit_cancel', commentEditCancelClick);

            FOS_COMMENT.thread_container.on('click', '.fos_comment_comment_vote', commentVoteClick);

            FOS_COMMENT.thread_container.on('click', '.fos_comment_comment_remove', commentRemoveClick);

            FOS_COMMENT.thread_container.on('click', '.fos_comment_thread_commentable_action', threadCommentableActionClick);
        },

        appendComment: function (commentHtml, form) {
            var form_data = form.data();

            if ('' != form_data.parent) {
                // reply button holder
                var reply_button_holder = form.closest('.fos_comment_comment_reply');

                var comment_element = form.closest('.fos_comment_comment_show')
                    .children('.fos_comment_comment_replies');

                reply_button_holder.removeClass('fos_comment_replying');

                comment_element.prepend(commentHtml);
                comment_element.trigger('fos_comment_add_comment', commentHtml);
            } else {
                // Insert the comment
                form.after(commentHtml);
                form.trigger('fos_comment_add_comment', commentHtml);

                // "reset" the form
                form = $(form[0]);
                form[0].reset();
                form.children('.fos_comment_form_errors').remove();
            }
        },

        editComment: function (commentHtml) {
            commentHtml = $($.trim(commentHtml));
            var originalCommentBody = $('#' + commentHtml.attr('id')).children('.fos_comment_comment_body');

            originalCommentBody.html(commentHtml.children('.fos_comment_comment_body').html());
        },

        cancelEditComment: function (commentBody) {
            commentBody.html(commentBody.data('original'));
        },

        /**
         * easyXdm doesn't seem to pick up 'normal' serialized forms yet in the
         * data property, so use this for now.
         * http://stackoverflow.com/questions/1184624/serialize-form-to-json-with-jquery#1186309
         */
        serializeObject: function (obj) {
            var o = {};
            var a = $(obj).serializeArray();
            $.each(a, function () {
                if (o[this.name] !== undefined) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        },

        loadCommentCounts: function () {
            var threadIds = [];
            var commentCountElements = $('span.fos-comment-count');

            commentCountElements.each(function (i, elem) {
                var threadId = $(elem).data('fosCommentThreadId');
                if (threadId) {
                    threadIds.push(threadId);
                }
            });

            FOS_COMMENT.get(
                FOS_COMMENT.base_url + '.json',
                {ids: threadIds},
                null,
                function (data) {
                    // easyXdm doesn't always serialize
                    if (typeof data != "object") {
                        data = jQuery.parseJSON(data);
                    }

                    var threadData = {};

                    for (var i in data.threads) {
                        threadData[data.threads[i].id] = data.threads[i];
                    }

                    $.each(commentCountElements, function () {
                        var threadId = $(this).data('fosCommentThreadId');
                        if (threadId) {
                            FOS_COMMENT.setCommentCount(this, threadData[threadId]);
                        }
                    });
                },
                'get'
            );

        },

        setCommentCount: function (elem, threadObject) {
            if (threadObject == undefined) {
                elem.innerHTML = '0';

                return;
            }

            elem.innerHTML = threadObject.num_comments;
        }
    };

    // Check if a thread container was configured. If not, use default.
    FOS_COMMENT.thread_container = window.fos_comment_thread_container || $('#fos_comment_thread');

    // AJAX via easyXDM if this is configured
    /*if (typeof window.fos_comment_remote_cors_url != "undefined") {
     /!**
     * easyXDM instance to use
     *!/
     FOS_COMMENT.easyXDM = easyXDM.noConflict('FOS_COMMENT');

     /!**
     * Shorcut request method.
     *
     * @param string method The request method to use.
     * @param string url The url of the page to request.
     * @param object data The data parameters.
     * @param function success Optional callback function to use in case of succes.
     * @param function error Optional callback function to use in case of error.
     *!/
     FOS_COMMENT.request = function (method, url, data, success, error) {
     // wrap the callbacks to match the callback parameters of jQuery
     var wrappedSuccessCallback = function (response) {
     if ('undefined' !== typeof success) {
     success(response.data, response.status);
     }
     };
     var wrappedErrorCallback = function (response) {
     if ('undefined' !== typeof error) {
     error(response.data.data, response.data.status);
     }
     };

     // todo: is there a better way to do this?
     FOS_COMMENT.xhr.request({
     url: url,
     method: method,
     data: data
     }, wrappedSuccessCallback, wrappedErrorCallback);
     };

     FOS_COMMENT.post = function (url, data, success, error) {
     this.request('POST', url, data, success, error);
     };

     FOS_COMMENT.get = function (url, data, success, error) {
     // make data serialization equals to that of jquery
     var params = jQuery.param(data);
     url += '' != params ? '?' + params : '';

     this.request('GET', url, undefined, success, error);
     };

     /!* Initialize xhr object to do cross-domain requests. *!/
     FOS_COMMENT.xhr = new FOS_COMMENT.easyXDM.Rpc({
     remote: window.fos_comment_remote_cors_url
     }, {
     remote: {
     request: {} // request is exposed by /cors/
     }
     });
     }*/

    // set the appropriate base url
    FOS_COMMENT.base_url = window.fos_comment_thread_api_base_url;

    // Load the comment if there is a thread id defined.
    /*if (typeof window.fos_comment_thread_id != "undefined") {
     // get the thread comments and init listeners
     FOS_COMMENT.getThreadComments(window.fos_comment_thread_id);
     }*/
    /*if (FOS_COMMENT.thread_container.length == 1) {
     FOS_COMMENT.getThreadComments(FOS_COMMENT.thread_container);
     } else if (FOS_COMMENT.thread_container.length > 1) {
     // get comments for all threads on page
     FOS_COMMENT.thread_container.each(function (i, e) {
     FOS_COMMENT.getThreadComments($(e));
     });
     }*/

    if (typeof window.fos_comment_thread_comment_count_callback != "undefined") {
        FOS_COMMENT.setCommentCount = window.fos_comment_thread_comment_count_callback;
    }

    if ($('span.fos-comment-count').length > 0) {
        FOS_COMMENT.loadCommentCounts();
    }

    // Initialize listeners on different elements
    FOS_COMMENT.initializeListeners();

    window.fos = window.fos || {};
    window.fos.Comment = FOS_COMMENT;


    function commentNewFormSubmit(e) {
        var that = $(this);
        var serializedData = FOS_COMMENT.serializeObject(this);

        e.preventDefault();

        var event = $.Event('fos_comment_submitting_form');
        that.trigger(event);

        if (event.isDefaultPrevented()) {
            return;
        }

        FOS_COMMENT.post(
            this.action,
            serializedData,
            e,
            // success
            function (data, statusCode) {
                FOS_COMMENT.appendComment(data, that);
                that.trigger('fos_comment_new_comment', data);
                if (that.data() && that.data().parent !== '') {
                    that.parents('.fos_comment_comment_form_holder').remove();
                }
            },
            // error
            function (data, statusCode) {
                var parent = that.parent();
                parent.after(data);
                parent.remove();
            },
            // complete
            function (data, statusCode) {
                that.trigger('fos_comment_submitted_form', statusCode);
            }
        );
    }

    function commentReplyShowFormClick(e) {
        var form_data = $(this).data();
        var that = $(this);

        if (that.closest('.fos_comment_comment_reply').hasClass('fos_comment_replying')) {
            return that;
        }

        FOS_COMMENT.get(
            form_data.url,
            {parentId: form_data.parentId},
            e,
            function (data) {
                that.closest('.fos_comment_comment_reply').addClass('fos_comment_replying');
                that.after(data);
                that.trigger('fos_comment_show_form', data);
            },
            'get'
        );
    }

    function commentReplyCancelClick(e) {
        var form_holder = $(this).closest('.fos_comment_comment_form_holder');

        var event = $.Event('fos_comment_cancel_form');
        form_holder.trigger(event);

        if (event.isDefaultPrevented()) {
            return;
        }

        form_holder.closest('.fos_comment_comment_reply').removeClass('fos_comment_replying');
        form_holder.remove();
    }

    function commentEditShowFormClick(e) {
        var form_data = $(this).data();
        var that = $(this);

        FOS_COMMENT.get(
            form_data.url,
            {},
            e,
            function (data) {
                var commentBody = $(form_data.container);

                // save the old comment for the cancel function
                commentBody.data('original', commentBody.html());

                // show the edit form
                commentBody.html(data);

                that.trigger('fos_comment_show_edit_form', data);
            },
            'get'
        );
    }

    function commentEditFormSubmit(e) {
        var that = $(this);

        FOS_COMMENT.post(
            this.action,
            FOS_COMMENT.serializeObject(this),
            e,
            // success
            function (data) {
                FOS_COMMENT.editComment(data);
                that.trigger('fos_comment_edit_comment', data);
            },

            // error
            function (data, statusCode) {
                var parent = that.parent();
                parent.after(data);
                parent.remove();
            }
        );

        e.preventDefault();
    }

    function commentEditCancelClick(e) {
        FOS_COMMENT.cancelEditComment($(this).parents('.fos_comment_comment_body'));
    }

    function commentVoteClick(e) {
        var that = $(this);
        var form_data = that.data();

        // Get the form
        FOS_COMMENT.get(
            form_data.url,
            {},
            e,
            function (data) {
                // Post it
                var form = $($.trim(data)).children('form')[0];
                var form_data = $(form).data();

                FOS_COMMENT.post(
                    form.action,
                    FOS_COMMENT.serializeObject(form),
                    null,
                    function (data) {
                        $('#' + form_data.scoreHolder).html(data);
                        that.trigger('fos_comment_vote_comment', data, form);
                    }
                );
            },
            'get'
        );
    }

    function commentRemoveClick(e) {
        var form_data = $(this).data();

        var event = $.Event('fos_comment_removing_comment');
        $(this).trigger(event);

        if (event.isDefaultPrevented()) {
            return;
        }

        // Get the form
        FOS_COMMENT.get(
            form_data.url,
            {},
            e,
            function (data) {
                // Post it
                var form = $($.trim(data)).children('form')[0];

                FOS_COMMENT.post(
                    form.action,
                    FOS_COMMENT.serializeObject(form),
                    null,
                    function (data) {
                        var commentHtml = $($.trim(data));

                        var originalComment = $('#' + commentHtml.attr('id'));

                        originalComment.replaceWith(commentHtml);
                    }
                );
            },
            'get'
        );
    }

    function threadCommentableActionClick(e) {
        var form_data = $(this).data();

        // Get the form
        FOS_COMMENT.get(
            form_data.url,
            {},
            e,
            function (data) {
                // Post it
                var form = $($.trim(data)).children('form')[0];

                FOS_COMMENT.post(
                    form.action,
                    FOS_COMMENT.serializeObject(form),
                    null,
                    function (data) {
                        var form = $($.trim(data)).children('form')[0];
                        var threadId = $(form).data().fosCommentThreadId;

                        // reload the intire thread
                        FOS_COMMENT.getThreadComments(threadId);
                    }
                );
            },
            'get'
        );
    }

})(window, window.jQuery, window.easyXDM);
