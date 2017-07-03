<?php
$config = $concept->config;
$attr = '';
if (!empty($concept->source_url)) {
    $attr .= ' htmlUrl="' . htmlentities($concept->source_url) . '"';
}
if (!empty($config->xmlUrl)) {
    $attr .= ' xmlUrl="' . htmlentities($config->xmlUrl) . '"';
    unset($config->xmlUrl);

    if (!empty($config->type)) {
        $attr .= ' type="' . htmlentities($config->type) . '"';
        unset($config->type);
    }
    else {
        $attr .= ' type="rss"';
    }
}
?>
<outline{!! $attr !!} id="{{$concept->id}}" text="{{$concept->title}}" summary="{{$concept->summary}}" config="{{ json_encode($config) }}">
    {!! $descendants !!}
</outline>