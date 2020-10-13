@extends('knowfox::layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Login</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}


                        @if (Auth::guest())
                            <p>On Knowfox, all content is private by default. To verify your identity, we need to send you an email with a link. Click this link and you get access to your account and the content you created yourself or that has been shared with you.</p>
                            <p><strong>Please enter your email address to log into Knowfox.</strong></p>
                        @endif

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">E-Mail Address</label>

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
                                    Login
                                </button>
                            </div>
                        </div>

                        <p><strong>Why do we have to send you an email?</strong> We use email to log you into Knowfox. This way, you don't have to remember a password. You stay logged in until you explicitly log out.</p>

                        <p><strong>Is this secure?</strong> Totally! To log into Knowfox, you have to click on that link in the email we send you. This way, nowbody has access to your Knowfox account, without also having access to your email account.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
