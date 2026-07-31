@extends('emails.layout')

@section('title', 'Wachtwoord opnieuw instellen')

@section('content')
    <p style="margin:0 0 16px 0; font-size:15px; line-height:1.6;">
        Beste {{ $user->name }},
    </p>

    <p style="margin:0 0 20px 0; font-size:15px; line-height:1.6;">
        Je hebt aangegeven je wachtwoord te willen herstellen. Klik op onderstaande knop om een nieuw wachtwoord te kiezen.
    </p>

    <table cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px 0;">
        <tr>
            <td style="background-color:#059669; border-radius:8px;">
                <a href="{{ $resetUrl }}" style="display:inline-block; padding:12px 20px; font-size:15px; font-weight:500; color:#ffffff; text-decoration:none;">
                    Nieuw wachtwoord kiezen
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
        Deze link is {{ $expireMinutes }} minuten geldig. Heb je dit niet zelf aangevraagd? Dan hoef je niets te doen.
    </p>
@endsection
