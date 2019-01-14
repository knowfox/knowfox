@extends('layouts.app')

@section('content')
    <div class="uk-container uk-margin-large uk-flex uk-flex-center">
        <div class="uk-card uk-card-default uk-width-1-2@s">
            <div class="uk-card-header">
                <h3 class="uk-card-title uk-margin-remove">@lang('Login')</h3>
            </div>
            <form class="uk-form-stacked" method="POST" action="{{ route('login') }}" novalidate>
                {{ csrf_field() }}
                <div class="uk-card-body">

                    <p>@lang('passwordless::login.above', ['app' => config('app.name')])</p>
                    <p><strong>@lang('passwordless::login.prompt')</strong></p>

                    <div class="uk-margin">
                        <label class="uk-form-label {{ $errors->has('email') ? ' uk-text-danger' : '' }}">
                            E-Mail Address
                        </label>
                        <div class="uk-width-1-1 uk-inline">
                            <span class="uk-form-icon {{ $errors->has('email') ? ' uk-text-danger' : '' }}" uk-icon="icon: user">
                            </span>
                            <input id="email" type="email" class="uk-input {{ $errors->has('email') ? ' uk-form-danger' : '' }}"
                            name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                        @if ($errors->has('email'))
                        <span class="uk-text-small uk-text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                </div>
                <div class="uk-card-footer uk-clearfix">
                    <button type="submit" class="uk-button uk-button-primary uk-box-shadow-medium">
                        Login
                    </button>
                    
                    @foreach (__('passwordless::login.below') as $question)
                    <p><strong>{{ strtr($question['title'], [':app' => config('app.name')]) }}</strong></p>
                    <p>{{ strtr($question['text'], [':app' => config('app.name')]) }}</p>
                    @endforeach
                </div>
            </form>
        </div>
    </div>
@endsection
