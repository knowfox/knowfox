@extends('layouts.app')

@section('content')

    <main class="container">

        <section class="page-header">
            <h1>Create concept</h1>

        </section>

        <form action="{{route('concept.store')}}" enctype="multipart/form-data" method="POST">
            {{csrf_field()}}

            @include('partials.concept-edit-form')

            <button type="submit" class="btn btn-primary">Save</button>
        </form>

    </main>

@endsection

@section('footer_scripts')

    <script>
        markdownEditor();
    </script>

@endsection