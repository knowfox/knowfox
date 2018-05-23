/**
 */
window.$ = window.jQuery = require('jquery');

const Concepts = {
    title: '',
    tree: {},
    breadcrumbs: [],
    current: null,

    getStorage: function () {
        const type = 'localStorage';
        try {
            var storage = window[type],
            x = '__storage_test__';
            storage.setItem(x, x);
            storage.removeItem(x);
            return storage;
        }
        catch(e) {
            return null;
        }
    },

    storage: null,

    load: function () {
        //this.storage = this.getStorage();

        $.ajax({
            url: '/api/concept/18',
            type: 'GET',
            dataType: 'json',
            data: {
                tag: 'chefkoch2'
            }
        })
            .then(data => {
                this.title = data.concept.title;
                this.current = data.concept;
                this.tree = [ data.children ];

                $(document).trigger('show-tree', this);
                this.showCurrent();
            });
    },

    downWedge: function () {
        //return '<i class="fa fa-caret-down"></i>';
        return '<i class="fa fa-angle-down"></i>';
    },

    rightWedge: function () {
        //return '<i class="fa fa-caret-right"></i>';
        return '<i class="fa fa-angle-right"></i>';
    },

    renderKids: function (result, node, id) {
        let markup = '';

        let nav = node.kids.length ? 'with-kids' : '';
        nav += node.open ? ' open' : '';
        nav += id == node.id ? ' current' : '';

        if (node.id == id) {
            this.current = node;
        }

        markup = '<li data-id="' + node.id + '" class="' + nav
            + '"><header><nav>'
            + this.downWedge()
            + this.rightWedge()
            + '</nav><h2>'
            + node.title + '</h2></header>';

        if (node.kids.length) {
            markup += this.renderTree(node.kids, id);
        }
        markup += '</li>';

        return result + markup;
    },

    renderTree: function (tree, id) {
        return '<ul>'
            + tree.reduce((acc, node) => this.renderKids(acc, node, id), '')
            + '</ul>';
    },

    getId: function () {
        const here = document.location.hash.match(/^#id:([\.\d]+)/);
        let id;
        if (here !== null) {
            id = here[1];
        }
        else {
            id = null;
        }
        return id;
    },

    /**
     * Find the current node in the tree
     * and set this.current to it.
     */
    updateCurrent: function (tree, id) {
        if (this.current && this.current.id == id) {
            return true;
        }

        for (let i in tree) {
            if (tree[i].id == id) {
                this.current = tree[i];
                return true;
            }
            if (tree[i].kids.length) {
                this.breadcrumbs.push(tree[i]);
                if (this.updateCurrent(tree[i].kids, id)) {
                    return true;
                }
                else {
                    this.breadcrumbs.pop();
                }
            }
        }

        return false;
    },

    showCurrent: function () {
        $(document).trigger('show-current', this.current);
    },
};

$(document).on('show-tree', (event, concepts) => {
    $('#tree').html('');
    $('#tree').append(concepts.renderTree(concepts.tree, concepts.getId()));
});

$(document).on('show-current', (event, concept) => {
    const breadcrumbs =
        Concepts.breadcrumbs.reduce((acc, node) => {
            let markup = '<a href="/#id:' + node.id + '">' + node.title + '</a>';
            return acc + ' / ' + markup;
        }, '<a href="/">Start</a>');

    $('#breadcrumbs').html(breadcrumbs);

    $('h1').text(concept.title);

    $('#editor article')
        .html(concept.body);

    if (concept.kids.length) {
        const kids = concept.kids.reduce((acc, node) => acc + '<li><a href="/#id:' + node.id + '">' + node.title + '</a></li>', '');
        $('#editor article')
            .append('<ul id="kids">' + kids + '</ul>');
    }

    $('#tree li.current').removeClass('current');
    $('#tree li[data-id="' + concept.id + '"]').addClass('current');
});

$(document).on('click', '#tree nav', function (event) {
    const $parent = $(this).closest('li');
    const $ul = $(this).parent().siblings('ul');

    if ($parent.hasClass('with-kids')) {
        if ($parent.hasClass('open')) {
            $ul.slideUp('fast', function () { $parent.removeClass('open'); });
        }
        else {
            $ul.slideDown('fast', function () { $parent.addClass('open'); });
        }
    }
});

$(document).on('click', '#tree header h2', function (event) {
    const id = $(this).parents('li').data('id');
    document.location.hash = '#id:' + id;
});

$(window).on('hashchange', function () {
    const id = Concepts.getId();
    Concepts.breadcrumbs = [];
    Concepts.updateCurrent(Concepts.tree, id);
    Concepts.showCurrent();
})

Concepts.load();
