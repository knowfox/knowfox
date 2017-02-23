@extends('layouts.app')

@section('content')

    <main class="container">

        @include('partials.concept-view-page-header')
        @include('partials.view-tabs', ['active' => 'graph'])

        <div id="graph-wrapper"></div>

    </main>

@endsection

@section ('footer_scripts')
    <script id="graph" type="dot">
    </script>

    <script>
    </script>
@endsection