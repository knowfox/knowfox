<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="theme/sky.css">
</head>
<body>
<div class="reveal">
    <div class="slides">
        <section data-uuid="{{$concept->uuid}}">
            <h1>{{$concept->title}}</h1>
            {!! $concept->rendered_body !!}
        </section>

        {!! $descendants !!}
    </div>
</div>
<script src="index.js"></script>
<script>
    Reveal.initialize();
</script>
</body>
</html>