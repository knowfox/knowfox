

<table class="table">
    <thead>
        <tr>
            <td>
                <select style="width:100%" name="emails[]" id="emails-input" multiple="multiple">
                    @foreach ($concept->shares as $share)
                        <option value="{{$share->email}}" selected="selected">{{$share->name}}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="permissions">
                    <option>Can view</option>
                    <option>Can edit</option>
                </select>
            </td>
            <td>
                <button class="btn btn-default">Add</button>
            </td>
        </tr>
    </thead>
    <tbody>
    @foreach ($concept->shares as $share)
        <tr>
            <td>{{$share->email}}</td>
            <td>
                <select>
                @foreach (['Can view', 'Can edit'] as $i => $permission)
                    <option value="{{$i}}"{!! $share->permissions == $i ? ' selected="selected"' : '' !!}>{{$permission}}</option>
                @endforeach
                </select>
            </td>
            <td>
                <button class="btn btn-default">Remove</button>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>