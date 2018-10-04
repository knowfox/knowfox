<html>
<head>
    <meta charset="utf-8">
    <title>Bookmarklet | Knowfox</title>
</head>
<body>
    <form id="knowfox-bookmark" action="{{route('bookmark.store')}}" method="POST">
        {{csrf_field()}}

        <input name="title" value="{{$concept->title}}">
        <input name="source_url" value="{{$concept->source_url}}">

        <button type="submit">Save</button>
    </form>
    <script>
        document.getElementById('knowfox-bookmark').submit();
    </script>
</body>
</html>
