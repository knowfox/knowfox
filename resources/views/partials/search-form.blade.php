<form action="{{ route('concept.index') }}"{!! !empty($class) ? " class=\"{$class}\"" : '' !!}>
    <div class="input-group">
        <input id="search-input" type="search" name="q" class="form-control" value="{{ $search_term or '' }}" placeholder="Search for...">
        <span class="input-group-btn">
            <button class="btn btn-default" type="button"><i class="glyphicon glyphicon-search"></i></button>
        </span>
    </div><!-- /input-group -->
</form>
