@extends('layouts.app')

@section('content')

    <main class="container">

        @include('partials.concept-view-page-header')
        @include('partials.view-tabs', ['active' => 'outline'])

        <div class="panel-container">

            <div class="panel-left" style="background-color:#888">
                <div id="outliner"></div>
            </div>

            <div class="panel-right">

            </div>
        </div>

    </main>

@endsection

@section ('footer_scripts')

    <script>
        $(".panel-left").resizable({
            handleSelector: ".splitter",
            resizeHeight: false
        });

        $('#outliner')
            .on('changed.jstree', function (e, data) {
                console.log("changed", data);
            })
            .on('create_node.jstree', function (e, data) {
                console.log("create_node", data);
            })
            .on('rename_node.jstree', function (e, data) {
                console.log("rename_node", data);
            })
            .on('delete_node.jstree', function (e, data) {
                console.log("delete_node", data);
            })
            .on('move_node.jstree', function (e, data) {
                console.log("move_node", data);
                axios.post('/json', {
                    op: 'move_node',
                    id: data.node.id,
                    text: data.node.text,
                    parent: data.parent,
                    position: data.position
                })
                .then(function (result) {
                    console.log("moved", result, status);
                    snackbar.show("Moved to parent #" + data.parent + ", pos #" + data.position);
                })
                .catch(function (result) {
                    console.log("NOT moved", result);
                });
            })
            .jstree({
                core: {
                    check_callback: true,
                    data: {
                        url: function (node) {
                            return node.id == '#'
                                ? '/json?id={{$concept->id}}'
                                : '/json?id=' + node.id;
                        }
                    },
                    themes: {
                        variant: 'dark'
                    },
                },
                plugins: [ 'dnd', 'types' ],
                types: {
                    default: {
                        icon : "glyphicon glyphicon-flash"
                    },
                    folder: {
                        icon: "glyphicon glyphicon-folder-open"
                    }
                }
        });
    </script>

@endsection