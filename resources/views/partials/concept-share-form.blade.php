@foreach ($concept->circles as $circle)
    @include('partials.circle-edit-form')
@endforeach
@include('partials.circle-edit-form', ['circle' => (object)[]])
