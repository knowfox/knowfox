<form action="{{ route('concept.index') }}"{!! !empty($class) ? " class=\"{$class}\"" : '' !!}>
    @if (!empty($concept))
        <input type="hidden" name="concept_id" value="{{ $concept->id }}">
    @endif
    <div class="input-group">
        <input id="search-input" type="search" name="q" class="form-control" value="{{ $search_term or '' }}" placeholder="Search {{ isset($concept) ? $concept->title : '' }}">
        <div class="input-group-btn">
            <button class="btn btn-default" type="button"><i class="glyphicon glyphicon-search"></i></button>
            @if (!empty($concept))
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu">
                    @foreach ($concept->ancestors()->get()->reverse() as $ancestor)
                        <li><a class="search-context" href="#" data-id="{{ $ancestor->id }}">&hellip; {{ $ancestor->title }}</a></li>
                    @endforeach
                    <li><a class="search-context" href="#">&hellip; globally</a></li>
                </ul>
            @endif
        </div>
    </div><!-- /input-group -->
</form>

@push('scripts')
    <script>
        $('a.search-context').click(function (e) {
            var id = $(this).data('id');

            e.preventDefault();

            if (id) {
                $('input[name=concept_id]').val(id);
            }
            else {
                $('input[name=concept_id]').val('');
            }

            $(this).parents('form').submit();
        });
    </script>
@endpush