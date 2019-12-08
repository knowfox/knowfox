@extends('layouts.app')

@section('content')
<div class="home container">
    @include('partials.messages')

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            @include('partials.search-form')
	</div>
	<div class="col-md-8 col-md-offset-2 text-center" style="margin-top:20px">
            <a target="_blank" href="https://www.buymeacoffee.com/knowfox"><img src="/img/logo-bmc.svg" width="200"></a>
        </div>
    </div>
</div>
@endsection
