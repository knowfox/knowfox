<html>
<head>
    <meta charset="utf-8">
    <title>Bookmarklet | Knowfox</title>
</head>
<body>
    <p style="font-family:helvetica sans-serif">{{$message}} -
        <a target="_top" href="{{ config('app.url') }}/concept/{{$concept->id}}">Bookmarked "{{$concept->title}}"</a>
    </p>
</body>
</html>