<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial, Helvetica, sans-serif; color:#374151;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f3f4f6; padding:24px 12px;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; background-color:#ffffff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">

                {{-- Header --}}
                <tr>
                    <td style="background-color:#059669; padding:20px 24px;">
                        <span style="color:#ffffff; font-size:20px; font-weight:bold;">{{ config('app.name') }}</span>
                    </td>
                </tr>

                {{-- Content --}}
                <tr>
                    <td style="padding:24px;">
                        <h1 style="margin:0 0 16px 0; font-size:22px; font-weight:500; color:#374151;">
                            @yield('title')
                        </h1>

                        @yield('content')
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background-color:#f9fafb; border-top:1px solid #e5e7eb; padding:16px 24px;">
                        <p style="margin:0; font-size:12px; color:#6b7280;">
                            Deze e-mail is automatisch verstuurd door {{ config('app.name') }}.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
