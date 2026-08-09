@extends('emails.layout')

@section('title', 'Nieuwe boeking')

@section('content')
    <p style="margin:0 0 20px 0; font-size:15px; line-height:1.6;">
        Er is een nieuwe boeking geplaatst. Hieronder staan de gegevens van de klant en de reservering.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb; border-radius:6px; font-size:14px;">
        <tr>
            <td style="padding:10px 12px; color:#6b7280; width:40%;">Reserveringsnummer</td>
            <td style="padding:10px 12px; font-weight:500;">{{ $arrangement->guid }}</td>
        </tr>
        <tr style="background-color:#f9fafb;">
            <td style="padding:10px 12px; color:#6b7280;">Klant</td>
            <td style="padding:10px 12px; font-weight:500;">{{ $arrangement->customer?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; color:#6b7280;">E-mailadres</td>
            <td style="padding:10px 12px; font-weight:500;">{{ $arrangement->customer?->email ?? '-' }}</td>
        </tr>
        <tr style="background-color:#f9fafb;">
            <td style="padding:10px 12px; color:#6b7280;">Telefoonnummer</td>
            <td style="padding:10px 12px; font-weight:500;">{{ $arrangement->customer?->phone_number ?: '-' }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; color:#6b7280;">Locatie</td>
            <td style="padding:10px 12px; font-weight:500;">{{ $arrangement->location?->name ?? '-' }}</td>
        </tr>
        <tr style="background-color:#f9fafb;">
            <td style="padding:10px 12px; color:#6b7280;">Aankomst</td>
            <td style="padding:10px 12px; font-weight:500;">{{ $arrangement->start_date?->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td style="padding:10px 12px; color:#6b7280;">Vertrek</td>
            <td style="padding:10px 12px; font-weight:500;">{{ $arrangement->end_date?->format('d-m-Y') }}</td>
        </tr>
        @if ($arrangement->total_price)
            <tr style="background-color:#f9fafb;">
                <td style="padding:10px 12px; color:#6b7280;">Totaalbedrag</td>
                <td style="padding:10px 12px; font-weight:500;">&euro; {{ number_format($arrangement->total_price, 2, ',', '.') }}</td>
            </tr>
        @endif
    </table>
@endsection
