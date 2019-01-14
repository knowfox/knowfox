@extends('layouts.app')

@section('content')
    <div class="uk-container uk-margin uk-flex uk-flex-center">
        <div class="uk-card uk-card-default uk-width-1-2@s">
            <div class="uk-card-header">
                <h3 class="uk-card-title uk-margin-remove">@lang('passwordless::register.title', ['app' => config('app.name')])</h3>
            </div>
            <form class="uk-form-stacked" method="POST" action="{{ route('register') }}" novalidate>

                {{ csrf_field() }}
                <div class="uk-card-body">
                    <p>@lang('passwordless::register.above', ['app' => config('app.name')])</p>
                    <p><strong>@lang('passwordless::register.prompt', ['app' => config('app.name')])</strong></p>       

                    <div class="uk-margin">
                        <label class="uk-form-label {{ $errors->has('name') ? ' uk-text-danger' : '' }}">
                            Name
                        </label>
                        <div class="uk-width-1-1 uk-inline">
                            <span class="uk-form-icon {{ $errors->has('name') ? ' uk-text-danger' : '' }}" uk-icon="icon: user">
                            </span>
                            <input id="name" type="name" class="uk-input {{ $errors->has('name') ? ' uk-form-danger' : '' }}"
                            name="name" value="{{ old('name') }}" required autofocus>
                        </div>
                        @if ($errors->has('email'))
                        <span class="uk-text-small uk-text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
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
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
