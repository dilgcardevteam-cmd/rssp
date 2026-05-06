@extends('layout.admin')
@section('title', 'System Manual')

@section('main-padding', 'px-4 sm:px-6 md:px-8')

@section('content')
<div class="pt-2 pb-6 sm:pb-10">
    <div class="max-w-6xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#0D2B70]">{{ $manualTitle }}</h1>
        <p class="text-sm text-slate-500 mt-2">This guide includes module screenshots, role actions, and step-by-step procedures.</p>

        <style>
            .manual-content img {
                display: block;
                width: 100%;
                max-width: 1100px;
                height: auto;
                border: 1px solid #cbd5e1;
                border-radius: 12px;
                margin-top: 10px;
                margin-bottom: 20px;
                background: #f8fafc;
            }

            .manual-content h2,
            .manual-content h3 {
                margin-top: 18px;
            }
        </style>

        <div class="manual-content prose prose-slate max-w-none mt-6">
            {!! $manualHtml !!}
        </div>
    </div>
</div>
@endsection
