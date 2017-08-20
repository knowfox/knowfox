<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="{{env('GOOGLE_SITE')}}" />
    <meta name="google-site-verification" content="{{env('GOOGLE_SITE2')}}" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if (!empty($uuid))
        <meta name="uuid" content="{{$uuid}}">
    @endif

    <title>@if (!empty($page_title)){{$page_title}} | @endif{{ config('app.name', 'Laravel') }}</title>

    <link rel="shortcut icon" type="image/x-icon" href="/img/knowfox-icon.ico">
    <link rel=”icon” type=”image/png” href=”/img/knowfox-icon.png”>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @yield('header_scripts')

    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body class="{{ str_replace('.', '-', Route::currentRouteName()) }}{{ Route::currentRouteName() != 'home' ? ' not-home' : '' }}">
    @section('navbar')
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                    @if (Auth::check())
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Concepts <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{route('concept.index')}}">All</a></li>
                                <li><a href="{{route('concept.toplevel')}}">Toplevel</a></li>
                                <li><a href="{{route('concept.popular')}}">Popular</a></li>
                                <li><a href="{{route('concept.flagged')}}">Flagged</a></li>
                                <li><a href="{{route('concept.shares')}}">Shared by me</a></li>
                                <li><a href="{{route('concept.shared')}}">Shared with me</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="{{route('concept.create')}}"><i class="glyphicon glyphicon-plus-sign"></i> New concept</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Tasks <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="{{route('item.todo')}}">Open</a></li>
                                <li><a href="{{route('item.done')}}">Completed</a></li>
                            </ul>
                        </li>

                        <li><a href="{{ route('journal') }}"><i class="glyphicon glyphicon-grain"></i> {{ strftime('%Y-%m-%d') }}</a></li>
                    @endif
                    </ul>

                    @if (Auth::check() && Route::currentRouteName() != 'home')
                        @include('partials.search-form', ['class' => 'desktop-only navbar-form navbar-left'])
                    @endif

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ route('login') }}">Login</a></li>
                            <li><a href="{{ route('register') }}">Register</a></li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a id="generate-token" href="#">API-Token</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>
    @show

    @yield('content')

    <footer class="footer">
        <div class="container">
            <p class="text-muted">
                &copy; {{ date('Y') }} Dr. Olav Schettler |
                <a href="javascript:(function(){d=document.createElement('iframe');d.style='position:fixed;z-index:9999;top:10px;right:10px;width:200px;height:200px;background:#FFF;';d.src='https://knowfox.com/bookmark?url='+encodeURIComponent(location.href)+'&title='+encodeURIComponent(document.title);document.body.appendChild(d);})()"><i class="glyphicon glyphicon-bookmark"></i><span class="desktop-only"> Bookmarklet</span></a>
                | <a href="https://knowfox.com/presentation/47d6c8de/013c/11e7/8a8c/56847afe9799/index.html">Features</a>
                | <a href="https://github.com/oschettler/knowfox/wiki">Getting started</a>
                | <a href="https://github.com/oschettler/knowfox" title="Knowfox is OpenSource. Download it on Github"><img style="height:16px" src="/img/github-32px.png"> OpenSource</a>
            </p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('footer_scripts')
</body>
</html>
