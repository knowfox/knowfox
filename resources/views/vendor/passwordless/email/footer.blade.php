<hr>

<table>
    <tr><td align="left">
        <p>@lang('passwordless::email.footer_text', [
            'app' => config('app.name'),
            'domain' => env('MAIL_DOMAIN')
        ])</p>

        @yield('cancel')

        <p>@lang('passwordless::email.footer_from', ['app' => config('app.name')])</p>
    </td></tr>
</table>
