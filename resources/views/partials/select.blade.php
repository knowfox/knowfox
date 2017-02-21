<select class="form-control" name="{{$name}}">
    @foreach ($options as $value)
        <option @if ($selected == $value) selected="selected" @endif>{{$value}}</option>
    @endforeach
</select>
