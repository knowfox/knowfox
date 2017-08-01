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