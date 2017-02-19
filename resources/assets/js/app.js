
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
var SimpleMDE = require('simplemde/dist/simplemde.min');

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

$('#concept-edit-form').on('shown.bs.modal', function (e) {
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
});