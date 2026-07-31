@extends('emails.layout')

@section('title', 'Welkom bij ' . config('app.name'))

@section('content')
    <p style="margin:0 0 16px 0; font-size:15px; line-height:1.6;">
        Beste {{ $user->name }},
    </p>

    <p style="margin:0 0 20px 0; font-size:15px; line-height:1.6;">
        Er is een account voor je aangemaakt met het e-mailadres <strong>{{ $user->email }}</strong>.
        Kies via onderstaande knop je eigen wachtwoord, daarna kun je inloggen.
    </p>

    <table cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px 0;">
        <tr>
            <td style="background-color:#059669; border-radius:8px;">
                <a href="{{ $resetUrl }}" style="display:inline-block; padding:12px 20px; font-size:15px; font-weight:500; color:#ffffff; text-decoration:none;">
                    Wachtwoord instellen
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 8px 0; font-size:13px; line-height:1.6; color:#6b7280;">
        Werkt de knop niet? Kopieer dan deze link naar je browser:
    </p>
    <p style="margin:0 0 20px 0; font-size:13px; line-height:1.6; word-break:break-all;">
        <a href="{{ $resetUrl }}" style="color:#059669;">{{ $resetUrl }}</a>
    </p>

    <p style="margin:0; font-size:13px; line-height:1.6; color:#6b7280;">
        Deze link is {{ $expireMinutes }} minuten geldig.
    </p>
@endsection
