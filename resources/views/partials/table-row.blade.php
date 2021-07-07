<tr>
    <td rowspan="2">
        @if (isset($concept->config->image))
            <img src="/uuid/{{ $concept->uuid }}/image?width=100">
        @endif
    </td>
    <td colspan="3">
        <div>
        @if ($concept->depth == 0)
            <a href="{{route('concept.show', ['concept' => $concept])}}">
                <strong>{{$concept->title}}</strong>
            </a>
            @else
            @foreach ($concept->ancestors()->get() as $ancestor)
            {{$ancestor->title}} &raquo;
            @endforeach
            <br>
            <a href="{{route('concept.show', ['concept' => $concept])}}">
                {{$concept->title}}
            </a>
        @endif
        @if ($concept->is_flagged)
            <i class="glyphicon glyphicon-heart"></i>
        @endif
        </div><div>{{ $concept->summary }}</div>
    </td>
    <td>{{strftime('%Y-%m-%d', strtotime($concept->created_at))}}</td>
</tr>
<tr>
    <td>
        @if ($concept->type != 'concept')
            <span class="label label-info">{{ucfirst($concept->type)}}</span>
        @endif
        @foreach ($concept->tags as $tag)
            <a href="{{route('concept.index', ['tag' => $tag->slug])}}" class="label label-default">{{$tag->name}}</a>
        @endforeach
    </td>
    <td>{{$concept->getDescendantCount()}}</td>
    <td>{{$concept->viewed_count}}</td>
    <td>{{$concept->viewed_at or $concept->updated_at}}</td>
</tr>
