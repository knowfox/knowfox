@extends('layouts.app')

@section('content')

    <main class="container">

        @include('partials.concept-view-page-header')
        @include('partials.view-tabs', ['active' => 'outline'])

        <div class="panel-container">

            <div class="panel-left" style="background-color:#fff;padding-left:10px">
                <div id="outliner" data-url="/json?node={{$concept->id}}"></div>
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
            .tree({
                dragAndDrop: true,
                saveState: true,
                closedIcon: $('<i class="glyphicon glyphicon-triangle-right"></i>'),
                openedIcon: $('<i class="glyphicon glyphicon-triangle-bottom"></i>')
            })
            .bind(
                'tree.move',
                function (event)
                {
                    var node = event.move_info.moved_node,
                        root_id = {{$concept->id}},
                        parent, next;

                    event.preventDefault();
                    event.move_info.do_move();

                    parent = node.parent;
                    next = node.getNextSibling();

                    axios.post('/json', {
                        op: 'move',
                        id: node.id,
                        parent: parent.id ? parent.id : root_id,
                        next: next ? next.id : null
                    });
                }
            )
            .bind(
                'tree.select',
                function (event)
                {
                    if (event.node) {
                        $('div.panel-right').html(
                            '<h2><a href="/' + event.node.id + '">' + event.node.name + '</a></h2>'
                            + (event.node.summary ? ('<p>' +  event.node.summary + '</p>') : '')
                            + '<div class="body">' + event.node.body + '</div>'
                        )
                    }
                    else {
                        $('div.panel-right').html('')
                    }
                }
            )
    </script>

@endsection