@extends('website.default.layout')

@push('header')
    <meta property="og:title" content="{{$concept->title}}" />
    <meta property="og:site_name" content="{{$config->title}}" />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="de_DE" />
    <link rel="canonical" href="https://schettler.net{{$concept->url}}" />
@endpush

@push('scripts')
    <script src="https://hypothes.is/embed.js" async></script>
@endpush