@extends('layouts.app')

@section('content')

    <main class="container">

        @include('partials.concept-view-page-header')
        @include('partials.view-tabs', ['active' => 'outline'])

        <div class="panel-container">

            <div class="panel-left">
                <div class="divOutlinerContainer">
                    <div id="outliner"></div>
                </div>
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
    </script>

    <script src="/concord/concordUtils.js"></script>

    <script>
        var when_last_keystroke = new Date(),
            save_ongoing = false;

        $("#outliner").concord ({
            prefs: {
                "outlineFont": "Hack, monospace",
                "outlineFontSize": 14,
                "outlineLineHeight": 22,
                "renderMode": true,
                "readonly": false,
                "typeIcons": appTypeIcons
            },
            callbacks: {
                opKeystroke: function () {
                    when_last_keystroke = new Date();
                },
                opCursorMoved: function (node) {
                    var config, summary,
                        title = node.getLineText(),
                        attr = node.getCursor().data('attributes');

                    if (attr) {
                        config = JSON.parse(attr.config);
                        summary = attr.summary;
                        console.log(summary, config);
                        $('.panel-right').html('<h2><a href="/' + attr.id + '">' + title + '</a></h2><p>' + summary + '</p><ul></ul>');
                        $.each(config, function (key, value) {
                            $('.panel-right ul').append('<li>' + key + '=' + value + '</li>')
                        });
                    }
                    else {
                        $('.panel-right').html('<h2>' + title + '</h2>');
                    }
                }
            }
        });
        $.get('/opml/' + {{$concept->id}}, function (opml) {
            opXmlToOutline(opml);
        });

        setInterval(function () {
            if (opHasChanged()) {
                if (!save_ongoing && secondsSince(when_last_keystroke) >= 1) {
                    console.log('Saving...');
                    save_ongoing = true;
                    $.ajax({
                        url: '/opml/' + {{$concept->id}},
                        type: 'POST',
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            opml: opOutlineToXml(
                                '{{$concept->owner->name}}',
                                '{{$concept->owner->email}}',
                                {{$concept->owner_id}}
                            )
                        },
                        error: function(res) {
                            console.log('Error', res);
                            save_ongoing = false;
                        },
                        success: function(res) {
                            console.log('Success', res);
                            opClearChanged();
                            save_ongoing = false;
                        }
                    });
                }
            }
        }, 1000);

    </script>
@endsection