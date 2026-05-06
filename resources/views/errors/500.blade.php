@extends('errors.minimal')

@section('title', '500 Internal Server Error')

@section('code', '500')
@section('heading', 'INTERNAL SERVER ERROR')
@section('message')
An unexpected error has occurred on the server.<br>
Please try again later or contact the Administrator.
@endsection
