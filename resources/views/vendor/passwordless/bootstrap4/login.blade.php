@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">

            <div class="col-md-6 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">@lang('Login')</h5>
                        <div class="card-text">
                           <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                                {{ csrf_field() }}


                                @if (Auth::guest())
                                    <p>
                                        @lang('On :app, all content is private by default.', ['app' => config('app.name')])
                                        @lang('To verify your identity, we need to send you an email with a link.')
                                        @lang('Click this link and you get access to your account.')
                                    </p>
                                    <p><strong>@lang('Please enter your email address to log into :app.', ['app' => config('app.name')])</strong></p>
                                @endif

                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                    <label for="email" class="col-md-4 control-label">@lang('E-Mail Address')</label>

                                    <div class="col-md-6">
                                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>

                                        @if ($errors->has('email'))
                                            <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-8 col-md-offset-4">
                                        <button type="submit" class="btn btn-primary">
                                            @lang('Login')
                                        </button>
                                    </div>
                                </div>

                                <p><strong>@lang('Why do we have to send you an email?')</strong>
                                    @lang('We use email to log you into :app.', ['app' => config('app.name')])
                                    @lang('This way, you don\'t have to remember a password.')
                                    @lang('You stay logged in until you explicitly log out.')
                                </p>

                                <p><strong>@lang('Is this secure?')</strong>
                                    @lang('Totally! To log into :app, you have to click on that link in the email we send you.', ['app' => config('app.name')])
                                    @lang('This way, nowbody has access to your :app account, without also having access to your email account.', ['app' => config('app.name')])
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
