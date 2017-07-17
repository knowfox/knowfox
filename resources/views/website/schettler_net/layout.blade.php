<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="{{$config->subtitle}}">
    <meta name="author" content="Dr. Olav Schettler">
    <link rel="icon" href="/favicon.ico">

    <title>{{$config->title}}</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <link href="/css/blog.css" rel="stylesheet">
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
                <h4>Über Olav</h4>
                <p>{!! $config->about !!}</p>
            </div>
            <div class="sidebar-module">
                <h4>Sonstwo</h4>
                <ol class="list-unstyled">
                    <li><a href="https://github.com/oschettler/knowfox">Personal Knowledge Management</a></li>
                    <li><a href="https://twitter.com/knowfox">Twitter</a></li>
                </ol>
            </div>
        </div><!-- /.blog-sidebar -->
    </div><!-- /.row -->
</div><!-- /.container -->

<footer class="blog-footer">
    <p>&copy; {{date('Y')}} by Dr. Olav Schettler.</p>
</footer>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>
