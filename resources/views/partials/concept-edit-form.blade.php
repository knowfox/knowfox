<div class="form-group">
    <label for="title">Title</label>
    <input type="text" class="form-control" name="title" id="title-input" value="{{$concept->title}}">
</div>

<div id="images" class="clearfix"></div>

<div class="form-group>
    <label for="body">Body</label>
    <textarea class="form-control" rows="10" name="body" id="body-input">{{$concept->body}}</textarea>
</div>

<div class="well">

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#taxonomy" aria-controls="taxonomy" role="tab" data-toggle="tab">Taxonomy</a></li>
        <li role="presentation"><a href="#summary" aria-controls="summary" role="tab" data-toggle="tab">Summary</a></li>
        <li role="presentation"><a href="#links" aria-controls="links" role="tab" data-toggle="tab">Links</a></li>
        <li role="presentation"><a href="#relations" aria-controls="relations" role="tab" data-toggle="tab">Relations</a></li>
        <li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Settings</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="taxonomy">
            <div class="form-group">
                <label for="parent_id">Parent</label>
                <select style="width:100%" name="parent_id" id="parent-input" data-except="{{$concept->id}}">
                    @if ($concept->parent_id)
                        <option value="{{$concept->parent_id}}" selected="selected">{{$concept->parent->title}}</option>
                    @endif
                </select>
            </div>

            <div class="form-group">
                <label for="tags">Tags</label>
                <select style="width:100%" name="tags[]" id="tags-input" multiple="multiple">
                    @foreach ($concept->tags as $tag)
                        <option value="{{$tag->slug}}" selected="selected">{{$tag->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="summary">
            <div class="form-group">
                <label for="summary">Summary</label>
                <textarea class="form-control" rows="3" name="summary" id="summary-input">{{$concept->summary}}</textarea>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="links">
            <div class="form-group">
                <label for="title">Source URL</label>
                <input type="text" class="form-control" name="source_url" id="source_url-input" value="{{$concept->source_url}}">
            </div>
            <div class="form-group">
                <label for="title">Slug</label>
                <div class="input-group">
                    <span class="input-group-addon" id="slug-prefix">https://knowfox.com/concept/</span>
                    <input type="text" class="form-control" name="slug" value="{{$concept->slug}}" aria-describedby="slug-prefix">
                </div>
            </div>
            <div class="form-group">
                <label for="title">Todoist ID</label>

                <div class="input-group">
                    <input type="text" class="form-control" name="todoist_id" id="todoist_id-input" placeholder="Todoist ID" aria-describedby="todoist-link">
                    @if ($concept->todoist_id)
                        <a target="todo" href="https://todoist.com/showTask?id={{$concept->todoist_id}}" class="input-group-addon" id="todoist-link">
                    @else
                        <a href="#" class="input-group-addon" id="todoist-link">
                    @endif
                    <i class="glyphicon glyphicon-eye-open"></i></a>
                </div>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="relations">
            <div class="form-group">
                <label for="relations">Relations</label>
                <textarea class="form-control" rows="3" name="relations" id="relations-input">{{$concept->relations}}</textarea>
                <p class="help-block">A YAML object. Each entry: <em>42: { type: uses }</em>. Defined relationship types: {{ join(', ', array_keys(config('knowfox.relationships'))) }}.</p>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="settings">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="title">Language</label>

                        @include('partials.select', [
                            'name' => 'language',
                            'selected' => $concept->language,
                            'options' => config('knowfox.languages')
                        ])
                    </div>
                    <div class="checkbox">
                        <label>
                            <input name="is_flagged" @if ($concept->is_flagged)checked="checked" @endif type="checkbox"> Flagged
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="title">Status</label>

                        @include('partials.select', [
                            'name' => 'status',
                            'selected' => $concept->status,
                            'options' => ['private', 'public']
                        ])
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="data">Data</label>
                        <textarea class="form-control" rows="3" name="data" id="data-input">{{$concept->data}}</textarea>
                        <p class="help-block">A YAML object.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

