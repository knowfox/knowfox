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

@endsection