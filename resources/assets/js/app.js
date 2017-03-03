
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('selectize');
require('concord/concord');

var SimpleMDE = require('simplemde/dist/simplemde.min');
window.Dropzone = require('dropzone');

require('jquery-ui/ui/widgets/autocomplete');
require('jquery-ui/ui/widgets/autocomplete');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 *

Vue.component('example', require('./components/Example.vue'));

const app = new Vue({
    el: '#app'
});
*/

window.markdownEditor = function () {
    var simplemde = new SimpleMDE({
        element: $("#body-input")[0],
        autofocus: true,
        autosave: {
            enabled: true
        },
        spellChecker: false,
        toolbar: [
            {
                name: "bold",
                action: SimpleMDE.toggleBold,
                className: "fa fa-bold",
                title: "Bold"
            },
            {
                name: "italic",
                action: SimpleMDE.toggleItalic,
                className: "fa fa-italic",
                title: "Italic"
            },
            {
                name: "heading-2",
                action: SimpleMDE.toggleHeading2,
                className: "fa fa-header fa-header-x fa-header-2",
                title: "Medium Heading"
            },
            "|",
            {
                name: "quote",
                action: SimpleMDE.toggleBlockquote,
                className: "fa fa-quote-left",
                title: "Blockquote"
            },
            {
                name: "unordered-list",
                action: SimpleMDE.toggleUnorderedList,
                className: "fa fa-list-ul",
                title: "Generic List"
            },
            {
                name: "ordered-list",
                action: SimpleMDE.toggleOrderedList,
                className: "fa fa-list-ol",
                title: "Numbered List"
            },
            "|",
            {
                name: "link",
                action: SimpleMDE.drawLink,
                className: "fa fa-link",
                title: "Create Link"
            },
            {
                name: "image",
                action: SimpleMDE.drawImage,
                className: "fa fa-picture-o",
                title: "Insert Image"
            },
            {
                name: "table",
                action: SimpleMDE.drawTable,
                className: "fa fa-table",
                title: "Insert Table"
            }
        ]
    });
};

$('#concept-edit-form').one('shown.bs.modal', markdownEditor);

$('#parent-input').selectize({
    persist: false,
    valueField: 'id',
    labelField: 'title',
    searchField: 'title',
    create: false,
    load: function(query, callback) {
        if (!query.length) return callback();
        $.ajax({
            url: '/concepts',
            type: 'GET',
            dataType: 'json',
            data: {
                except: $(this).get(0).$input.data('except'),
                q: query
            },
            error: function() {
                callback();
            },
            success: function(res) {
                callback(res.data);
            }
        });
    }
});

$('#tags-input').selectize({
    delimiter: ',',
    persist: false,
    valueField: 'slug',
    labelField: 'name',
    searchField: 'name',
    create: function(input) {
        return {
            slug: input,
            name: input
        }
    },
    load: function(query, callback) {
        if (!query.length) return callback();
        $.ajax({
            url: '/tags',
            type: 'GET',
            dataType: 'json',
            data: {
                q: query
            },
            error: function() {
                callback();
            },
            success: function(res) {
                callback(res.data);
            }
        });
    }
});

$('#emails-input').selectize({
    delimiter: ',',
    persist: false,
    valueField: 'email',
    labelField: 'name',
    searchField: 'name',
    create: function(input) {
        return {
            email: input,
            name: input
        }
    },
    load: function(query, callback) {
        if (!query.length) return callback();
        $.ajax({
            url: '/emails',
            type: 'GET',
            dataType: 'json',
            data: {
                q: query
            },
            error: function() {
                callback();
            },
            success: function(res) {
                callback(res.data);
            }
        });
    }
});

$.widget( "custom.kfAutocomplete", $.ui.autocomplete, {
    _normalize: function (items) {
        return $.map(items.data, function(item) {
            return {
                value: item.id,
                label: item.title
            };
        });
    }
});

$('#search-input').kfAutocomplete({
    source: '/concepts?limit=10',
    minLength: 2,
    select: function(event, ui) {
        var url = '/concept/' + ui.item.value;

        event.preventDefault();
        location.href = url;
    }
});

$('#todoist_id-input').on('keyup', function () {
    var id = $(this).val();
    if (id === '') {
        $('#todoist-link').on('click', function(e) {
            e.preventDefault();
        });
    }
    else {
        $('#todoist-link').off('click');
    }
    $('#todoist-link').attr('href', 'https://todoist.com/showTask?id=' + id);
});
