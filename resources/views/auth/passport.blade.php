@extends('layouts.app')

@section('content')

    <main class="container">

        <section class="page-header">

            <ol class="breadcrumb">
                <li class="active">Passport</li>
            </ol>

            <h1>{{$page_title}} <small>{{$sub_title}}</small></h1>
            @include('partials.messages')

        </section>

        <div id="app">
            <passport-clients></passport-clients>
            <passport-authorized-clients></passport-authorized-clients>
            <passport-personal-access-tokens></passport-personal-access-tokens>
        </div>

    </main>
@endsection

@push('scripts')
    <script>
        const app = new Vue({
            el: '#app'
        });
    </script>
@endpush