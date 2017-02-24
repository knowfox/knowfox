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
            $("#outliner").concord ({
                "prefs": {
                    "outlineFont": "Georgia",
                    "outlineFontSize": 18,
                    "outlineLineHeight": 24,
                    "renderMode": false,
                    "readonly": false,
                    "typeIcons": appTypeIcons
                },
            });
            $.get('/concept/' + {{$concept->id}} + '/opml', function (opml) {
                opXmlToOutline(opml);
            });
    </script>
@endsection