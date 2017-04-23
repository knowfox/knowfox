<div class="blog-post">
    @if ($concept->image_src)
        <a href="/{{$concept->slug}}/">
            <img class="blog-post-thumbnail thumbnail" src="{{$concept->image_src}}">
        </a>
    @endif
        <h2 class="blog-post-title"><a href="/{{$concept->slug}}/">{{$concept->title}}</a></h2>
        <p class="blog-post-meta">{{ strftime('%Y-%m-%d', strtotime($concept->created_at)) }} by {{$concept->owner->name}}</p>

    <p>{{$concept->summary}}</p>
</div>
