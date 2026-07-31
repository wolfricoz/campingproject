@extends('emails.layout')

@section('title', $title)

@section('content')
    <div style="font-size:15px; line-height:1.6;">
        {!! nl2br(e($text)) !!}
    </div>
@endsection
