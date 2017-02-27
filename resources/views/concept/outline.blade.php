@extends('layouts.app')

@section('content')

    <main class="container">

        @include('partials.concept-view-page-header')
        @include('partials.view-tabs', ['active' => 'outline'])

        <div class="divOutlinerContainer">
            <div id="outliner"></div>
        </div>

    </main>

@endsection

@section ('footer_scripts')
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