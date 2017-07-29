@extends('website.default.layout')

@push('header')
    <meta name="google-site-verification" content="_KtWrrDb_zGhZ_GEtxg5LOYfbrX50SV1b4P3ZDVNQVg" />
    <meta property="og:title" content="{{$concept->title}}" />
    <meta property="og:site_name" content="{{$config->title}}" />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="en_US" />
    <link rel="canonical" href="https://blog.knowfox.com{{$concept->url}}" />
@endpush
