@extends('concept.show')

<?php use \Illuminate\Support\Facades\Input; ?>

@section('main-content')
    @parent

    @if ($concept->children()->count())

        <?php
            $letters = [];
            foreach ($concept->letters()->get() as $letter) {
                if ($letter->t < 'A') {
                    if (empty($letters['#'])) {
                        $letters['#'] = 0;
                    }
                    $letters['#'] += $letter->n;
                }
                else {
                    $uppercase = ucfirst($letter->t);
                    if (empty($letters[$uppercase])) {
                        $letters[$uppercase] = 0;
                    }
                    $letters[$uppercase] += $letter->n;
                }
            }

            $books = $concept->children();
            if (Input::has('letter')) {
                $letter = ucfirst(substr(Input::input('letter'), 0, 1));
                if ($letter < 'A' || $letter > 'Z') {
                    $books = $books->where('title', '<', 'A');
                }
                else {
                    $books = $books
                        ->where('title', '>=', $letter)
                        ->where('title', '<', chr(ord($letter) + 1));
                }
            }
            $books = $books
                ->orderBy('title', 'asc')
                ->paginate();
            if (Input::has('letter')) {
                $books->appends('letter', $letter);
            }
        ?>
        <ul class="letters">
        @foreach ($letters as $letter => $count)
            <li>
                <a href="?letter={{$letter}}" title="{{$count}}">{{$letter}}</a>
            </li>
        @endforeach
        </ul>

        <table class="table">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Author</th>
                    <th>Title</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($books as $book)
                <tr>
                    <td>
                    @if (!empty($book->config->image))
                        <img src="/{{$book->id}}/{{$book->config->image}}?style=thumbnail">
                    @endif
                    </td>
                    <td>{{$book->config->author or ''}}</td>
                    <td><a href="{{route('concept.show', ['concept' => $book])}}">
                        {{$book->title}}
                        </a>
                    </td>
                    <td>{{$book->config->year or ''}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="text-center">{{$books}}</div>

    @endif

@endsection

@section('children')
@endsection

@section('siblings')
@endsection