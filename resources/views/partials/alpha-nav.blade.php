<ul class="letters">
    @foreach ($letters as $letter => $count)
        <li>
            <a href="?letter={{$letter}}" title="{{$count}}">{{$letter}}</a>
        </li>
    @endforeach
</ul>
