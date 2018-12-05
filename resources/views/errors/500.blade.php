@extends('layouts.app')

@section('content')

    <main class="container">

        <section class="page-header">

            <h1>Error 500</h1>

            @include('partials.messages')

        </section>

        <p>Sorry for this.</p>

        <?php error_log(json_encode(func_get_args()), 3, '/tmp/knowfox-500.log'); ?>

    </main>
@endsection