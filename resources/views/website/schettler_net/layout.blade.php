@extends('website.default.layout')

@push('header')
    <meta name="google-site-verification" content="S0VR4HXWxYcwebMQxarTHx-r6CjZwxmcKmbamC3exGI" />
    <meta property="og:title" content="{{$concept->title}}" />
    <meta property="og:site_name" content="{{$config->title}}" />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="de_DE" />
    <link rel="canonical" href="https://schettler.net{{$concept->url}}" />
@endpush

@push('scripts')
    <script src="https://hypothes.is/embed.js" async></script>
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-3547732-10', 'auto');
        ga('send', 'pageview');

    </script>
@endpush