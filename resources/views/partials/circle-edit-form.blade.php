<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            <label for="circle">{{ empty($circle->id) ? 'New ' : '' }}Circle</label>
            <select style="width:100%" name="circle[]" class="circle-input">
                @if (!empty($circle->id)))
                    <option value="{{$circle->id}}" selected="selected">{{$circle->name}}</option>
                @endif
            </select>
        </div>
    </div>
    <div class="col-sm-offset-4 col-sm-3">
        <div class="checkbox">
            <label>
                <input name="view[]" @if (!empty($circle->pivot->view) && $circle->pivot->view)checked="checked" @endif type="checkbox"> View
            </label>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="checkbox">
            <label>
                <input name="edit[]" @if (!empty($circle->pivot->edit) && $circle->pivot->edit)checked="checked" @endif type="checkbox"> Edit
            </label>
        </div>
    </div>
</div>
