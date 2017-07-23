<!DOCTYPE html>
<html lang="{{$lang or 'en'}}"{!! $html_attr or '' !!}>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="{{$config->subtitle}}">
    <meta name="author" content="{{$config->author or 'Dr. Olav Schettler'}}">
    <meta name="generator" content="https://knowfox.com">
    <link rel="icon" href="/favicon.ico">

    <title>{{$concept->title != $config->title ? "{$concept->title} | " : ''}}{{$config->title}}</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <link href="//cdn.rawgit.com/noelboss/featherlight/1.7.7/release/featherlight.min.css" type="text/css" rel="stylesheet">
    <link href="//cdn.rawgit.com/noelboss/featherlight/1.7.7/release/featherlight.gallery.min.css" type="text/css" rel="stylesheet">

    <link href="/css/blog.css" rel="stylesheet">

    @stack('header')
</head>

<body>

<div class="blog-masthead">
    <div class="container">
        <nav class="blog-nav">
            @foreach ($config->navigation as $item)
                <a class="blog-nav-item" href="{{$item['url']}}">{{$item['title']}}</a>
            @endforeach
        </nav>
    </div>
</div>

<div class="container">

    @yield('header')

    <div class="row">

        <div class="col-sm-8 blog-main">

            @yield('content')

        </div><!-- /.blog-main -->

        <div class="col-sm-3 col-sm-offset-1 blog-sidebar">
            <div class="sidebar-module sidebar-module-inset">
                <h4>{{$config->about_title or 'About'}}</h4>
                <p>{!! $config->about !!}</p>
            </div>

            @if (!empty($config->links))
                <div class="sidebar-module">
                    <h4>{{$config->link_title or 'Elsewhere'}}</h4>
                    <ol class="list-unstyled">
                    @foreach ($config->links as $link)
                        <li><a href="{{$link['url']}}">{{$link['title']}}</a></li>
                    @endforeach
                    </ol>
                </div>
            @endif
        </div><!-- /.blog-sidebar -->
    </div><!-- /.row -->
</div><!-- /.container -->

<footer class="blog-footer">
    <p>&copy; {{date('Y')}} by {{$config->author or 'Dr. Olav Schettler'}}.</p>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script src="//cdn.rawgit.com/noelboss/featherlight/1.7.7/release/featherlight.min.js" type="text/javascript" charset="utf-8"></script>
<script src="//cdn.rawgit.com/noelboss/featherlight/1.7.7/release/featherlight.gallery.min.js" type="text/javascript" charset="utf-8"></script>
@stack('scripts')
</body>
</html>
