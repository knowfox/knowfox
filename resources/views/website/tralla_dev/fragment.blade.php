<div class="blog-post">
    <h2 class="blog-post-title"><a href="/{{$concept->slug}}">{{$concept->title}}</a></h2>
    <p class="blog-post-meta">{{ strftime('%Y-%m-%d', strtotime($concept->created_at)) }} by {{$concept->owner->name}}</p>

    <p>{{$concept->summary}}</p>
</div>
