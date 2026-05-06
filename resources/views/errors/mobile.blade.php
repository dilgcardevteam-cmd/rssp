{{-- resources/views/errors/mobile.blade.php --}}
@extends('errors.minimal')

@section('title', '📵 Mobile Not Supported')
@section('code', '📵')
@section('heading', 'MOBILE ACCESS BLOCKED')
@section('message')
  This application is not available on mobile devices.<br>
  Please switch to a desktop or tablet with a larger screen.
@endsection
