<hr>

<p>We improve {{config('app.name')}} every day. If you have suggestions, we love to hear from you.
    <a href="mailto:hello{{ '@' . env('MAIL_DOMAIN') }}">hello{{ '@' . config('app.name')}}</a>
</p>

@yield('cancel')

<p>Sent from {{config('app.name')}} c/o Olav Schettler, Pipinstr. 14, D-53111 Bonn, Germany</p>
