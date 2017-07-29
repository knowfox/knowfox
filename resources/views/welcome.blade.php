<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Knowfox</title>

        <meta name="description" content="Personal knowledge management">
        <meta name="google-site-verification" content="{{env('GOOGLE_SITE')}}" />
        <meta name="google-site-verification" content="{{env('GOOGLE_SITE2')}}" />

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            .alert-success {
                background-color: #dff0d8;
                border-color: #d6e9c6;
                color: #3c763d;
            }

            .alert {
                padding: 15px;
                margin-bottom: 22px;
                border: 1px solid transparent;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @if (Auth::check())
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ url('/login') }}">Login</a>
                        <a href="{{ url('/register') }}">Register</a>
                    @endif
                </div>
            @endif

            <div class="content">

                @include('partials.messages')

                <div class="title m-b-md">
                    Knowfox
                </div>

                <div class="links">
                    <p><strong>Personal Knowledge Management</strong></p>

                    <p>
                        <a href="https://github.com/oschettler/knowfox/wiki">Install</a> locally or on your own server
                        | <a href="https://github.com/oschettler/knowfox">Open Source</a> Software
                    </p>

                    <p>
                        Hierarchies, outlining, and tags<br>
                        Typed, bidirektional relationships<br>
                        Markdown<br>
                        Full text search<br>
                        Upload images and attachments<br>
                        Bookmarking<br>
                        Private as default<br>
                        Easy journalling<br>
                        Sharing and publishing as slide deck<br>
                        Import from Evernote
                    </p>

                </div>
            </div>
        </div>
    </body>
</html>
