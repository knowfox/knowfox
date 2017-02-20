<div class="form-group">
    <label for="title">Title</label>
    <input type="text" class="form-control" name="title" id="title-input" value="{{$concept->title}}">
</div>

<div class="form-group">
    <label for="summary">Body</label>
    <textarea class="form-control" rows="10" name="body" id="body-input">{{$concept->body}}</textarea>
</div>


<div class="well">

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#parents" aria-controls="parents" role="tab" data-toggle="tab">Parents & Tabs</a></li>
        <li role="presentation"><a href="#summary" aria-controls="summary" role="tab" data-toggle="tab">Summary</a></li>
        <li role="presentation"><a href="#source" aria-controls="source" role="tab" data-toggle="tab">Source</a></li>
        <li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Settings</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="parents">
            <div class="form-group">
                <label for="parent_id">Parent</label>
                <select style="width:100%" name="parent_id" id="parent-input" data-except="{{$concept->id}}">
                    @if ($concept->parent_id)
                        <option value="{{$concept->parent_id}}" selected="selected">{{$concept->parent->title}}</option>
                    @endif
                </select>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="summary">
            <div class="form-group">
                <label for="summary">Summary</label>
                <textarea class="form-control" rows="3" name="summary" id="summary-input">{{$concept->summary}}</textarea>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="source">
            <div class="form-group">
                <label for="title">Source URL</label>
                <input type="text" class="form-control" name="source_url" id="source_url-input" value="{{$concept->source_url}}">
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="settings">...</div>
    </div>

</div>
