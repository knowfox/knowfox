@extends('website.default.layout')

@push('header')
    <meta name="google-site-verification" content="{{$config->google_site}}" />
    <meta property="og:title" content="{{$concept->title}}" />
    <meta property="og:site_name" content="{{$config->title}}" />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="de_DE" />
    <link rel="canonical" href="https://schettler.net{{$concept->url}}" />
    <link rel="openid.server" href="https://id.schettler.net/MyID.config.php">
    <link rel="openid.delegate" href="https://id.schettler.net/MyID.config.php">
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Person",
      "name": "Dr. Olav Schettler",
      "url": "https://schettler.net",
      "sameAs": [
        "https://www.facebook.com/olav.schettler",
        "https://www.xing.com/profile/Olav_Schettler",
        "https://www.linkedin.com/in/olavschettler/",
        "https://plus.google.com/u/0/+OlavSchettlerBonn"
      ]
    }
    </script>

    <link rel="apple-touch-icon" sizes="57x57" href="/icons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/icons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/icons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/icons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/icons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/icons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/icons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/icons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/icons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">
    <link rel="manifest" href="/icons/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/icons/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
@endpush

@push('scripts')
    <script src="https://hypothes.is/embed.js" async></script>
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', '{{$config->google_analytics}}', 'auto');
        ga('send', 'pageview');

    </script>
@endpush