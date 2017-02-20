<div class="form-group">
    <label for="parent_id">Parent</label>
    <select style="width:100%" name="parent_id" id="parent-input" data-except="{{$concept->id}}">
        @if ($concept->parent_id)
            <option value="{{$concept->parent_id}}" selected="selected">{{$concept->parent->title}}</option>
        @endif
    </select>
</div>
<div class="form-group">
    <label for="title">Title</label>
    <input type="text" class="form-control" name="title" id="title-input" value="{{$concept->title}}">
</div>
<div class="form-group">
    <label for="summary">Summary</label>
    <textarea class="form-control" rows="3" name="summary" id="summary-input">{{$concept->summary}}</textarea>
</div>
<div class="form-group">
    <label for="summary">Body</label>
    <textarea class="form-control" rows="10" name="body" id="body-input">{{$concept->body}}</textarea>
</div>
