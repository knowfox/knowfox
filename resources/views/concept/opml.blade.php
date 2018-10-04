<?xml version="1.0" encoding="UTF-8"?>
<opml version="2.0">
    <head>
        <title>{{$concept->title}}</title>
        <dateCreated>{{$concept->created_at}}</dateCreated>
        <dateModified>{{$concept->updated_at}}</dateModified>
        <ownerName>{{$concept->owner->name}}</ownerName>
        <ownerEmail>{{$concept->owner->email}}</ownerEmail>
        <docs>http://dev.opml.org/spec2.html</docs>
    </head>
    <body>
        {!! $tree !!}
    </body>
</opml>