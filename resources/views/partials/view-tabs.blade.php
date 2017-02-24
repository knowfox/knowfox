<!-- Nav tabs -->
<ul class="nav nav-tabs">
@foreach ([
    'view' => 'Standard',
    'outline' => 'Outline',
] as $tab => $label)
    <li role="presentation"{!! $active == $tab ? ' class="active"' : '' !!}><a href="{{$tab}}" role="tab">{{$label}}</a></li>
@endforeach
</ul>