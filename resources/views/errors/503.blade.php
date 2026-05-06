@extends('errors.minimal')

@section('title', '503 Service Unavailable')

@section('code', '503')
@section('heading', 'SERVICE UNAVAILABLE')
@section('message')
The server is currently unable to handle the request due to temporary overloading or maintenance of the server.<br>Please try again later.
@endsection
