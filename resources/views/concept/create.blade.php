@extends('knowfox::layouts.app')

@section('content')

    <main class="container">

        <section class="page-header">
            <h1>Create {{$concept->parent_id ? 'sub-' : ''}}concept</h1>

            @include('knowfox::partials.messages')

        </section>

        <form action="{{route('concept.store')}}" enctype="multipart/form-data" method="POST">
            {{csrf_field()}}

            @include('knowfox::partials.concept-edit-form')

            <button type="submit" class="btn btn-primary">Save</button>
        </form>

    </main>

@endsection

@section('footer_scripts')

    <script>
        markdownEditor();

        Dropzone.options.dropzone = {
            maxFilesize: 30,
            url: '/upload/{{$concept->uuid}}',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
            //, previewsContainer: '#dropzone-preview'
        };
    </script>

@endsection