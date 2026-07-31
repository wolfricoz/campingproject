@extends('emails.layout')

@section('title', 'Betaling ontvangen')

@section('content')
    <p style="margin:0 0 16px 0; font-size:15px; line-height:1.6;">
        Beste {{ $arrangement->customer?->name ?? 'gast' }},
    </p>

    <p style="margin:0 0 20px 0; font-size:15px; line-height:1.6;">
        We hebben je betaling ontvangen. Je reservering staat hiermee definitief vast.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb; border-radius:6px; font-size:14px;">
        <tr>
            <td style="padding:10px 12px; color:#6b7280; width:40%;">Reserveringsnummer</td>
            <td style="padding:10px 12px; font-weight:500;">{{ $arrangement->guid }}</td>
        </tr>
        <tr style="background-color:#f9fafb;">
            <td style="padding:10px 12px; color:#6b7280;">Locatie</td>
            <td style="padding:10px 12px; font-weight:500;">{{ $arrangement->location?->name ?? '-' }}</td>
        </tr>
        @if ($arrangement->total_price)
            <tr>
                <td style="padding:10px 12px; color:#6b7280;">Betaald bedrag</td>
                <td style="padding:10px 12px; font-weight:500;">&euro; {{ number_format($arrangement->total_price, 2, ',', '.') }}</td>
            </tr>
        @endif
    </table>

    <p style="margin:20px 0 0 0; font-size:15px; line-height:1.6;">
        Bedankt en tot ziens!
    </p>
@endsection
