@extends('layout.app')
@section('title', 'About Us')

@section('content')
    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h1 class="flex w-full items-center gap-3 border-b border-[#0D2B70] py-2 font-montserrat text-4xl tracking-wide text-[#0D2B70]">
            About DILG-CAR
        </h1>
    </section>

    <section class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="about-hero relative overflow-hidden rounded-3xl border border-[#0D2B70]/10 bg-gradient-to-br from-[#0D2B70] via-[#0A2259] to-[#06153A] px-6 py-8 text-white shadow-2xl md:px-10 md:py-10">
            <div class="about-hero-glow-left absolute -left-16 -top-16 h-40 w-40 rounded-full bg-[#FFDE15]/20 blur-2xl"></div>
            <div class="about-hero-glow-right absolute -bottom-16 -right-12 h-44 w-44 rounded-full bg-[#2787F5]/20 blur-2xl"></div>

            <div class="relative z-10 grid gap-6 lg:grid-cols-[1.3fr,0.7fr] lg:items-end">
                <div>
                    <p class="inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.2em] text-white/90">
                        DILG-CAR
                    </p>
                    <h2 class="mt-4 max-w-3xl font-montserrat text-2xl font-extrabold leading-tight sm:text-3xl">
                        A highly trusted Department and Partner in nurturing local governments.
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-blue-100 sm:text-base">
                        DILG-CAR advances peaceful, safe, progressive, resilient, and inclusive communities toward a comfortable and secure life for Filipinos by 2040.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur-sm">
                        <p class="text-[11px] uppercase tracking-wide text-blue-100">Target Year</p>
                        <p class="mt-1 text-2xl font-extrabold">2040</p>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur-sm">
                        <p class="text-[11px] uppercase tracking-wide text-blue-100">Core Focus</p>
                        <p class="mt-1 text-sm font-bold">Peace, Safety, Governance</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto mt-8 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <article class="about-card group rounded-3xl border border-[#0D2B70]/15 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl md:p-8">
                <div class="mb-5 inline-flex rounded-2xl bg-blue-50 p-3 text-[#0D2B70]">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="font-montserrat text-2xl font-extrabold text-[#0D2B70]">Mission</h3>
                <p class="mt-4 text-sm leading-relaxed text-slate-700 sm:text-base">
                    The Department shall ensure peace and order, public safety and security, uphold excellence in local governance and enable resilient and inclusive communities.
                </p>
            </article>

            <article class="about-card group rounded-3xl border border-red-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl md:p-8">
                <div class="mb-5 inline-flex rounded-2xl bg-red-50 p-3 text-red-600">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
                <h3 class="font-montserrat text-2xl font-extrabold text-red-600">Vision</h3>
                <p class="mt-4 text-sm leading-relaxed text-slate-700 sm:text-base">
                    A highly trusted Department and Partner in nurturing local governments and sustaining peaceful, safe, progressive, resilient, and inclusive communities towards a comfortable and secure life for Filipinos by 2040.
                </p>
            </article>

            <article class="about-card rounded-3xl border border-yellow-200 bg-gradient-to-br from-[#FFFCE8] via-[#FFF8C6] to-[#FFEFA8] p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl lg:col-span-2 md:p-8">
                <div class="mb-4 inline-flex rounded-2xl bg-white/80 p-3 text-amber-600">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <h3 class="font-montserrat text-2xl font-extrabold text-amber-700">Shared Values</h3>
                <p class="mt-3 text-sm font-semibold text-slate-700 sm:text-base">Ang DILG ay Matino, Mahusay at Maaasahan.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="rounded-full border border-amber-300 bg-white/80 px-4 py-1.5 text-xs font-bold text-amber-700">Matino</span>
                    <span class="rounded-full border border-amber-300 bg-white/80 px-4 py-1.5 text-xs font-bold text-amber-700">Mahusay</span>
                    <span class="rounded-full border border-amber-300 bg-white/80 px-4 py-1.5 text-xs font-bold text-amber-700">Maaasahan</span>
                </div>
            </article>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[0.75fr,1.25fr]">
            <div>
                <p class="inline-flex rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-700">Contact</p>
                <h3 class="mt-3 font-montserrat text-2xl font-extrabold text-slate-800">Contact Information</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    For official key contacts, visit
                    <a href="https://car.dilg.gov.ph/key-officials/" target="_blank" rel="noopener"
                        class="font-semibold text-blue-700 underline decoration-blue-300 underline-offset-2 transition hover:text-blue-900">
                        DILG-CAR Key Officials
                    </a>.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700">General Email</h4>
                    <a href="mailto:dilgcarcloud@gmail.com"
                        class="mt-2 block break-all text-sm font-semibold text-blue-700 transition hover:text-blue-900 hover:underline">
                        dilgcarcloud@gmail.com
                    </a>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700">HR & Records</h4>
                    <div class="mt-2 space-y-1">
                        <a href="mailto:dilgcar.hr@gmail.com"
                            class="block break-all text-sm font-semibold text-blue-700 transition hover:text-blue-900 hover:underline">
                            dilgcar.hr@gmail.com
                        </a>
                        <a href="mailto:dilgcarfad@gmail.com"
                            class="block break-all text-sm font-semibold text-blue-700 transition hover:text-blue-900 hover:underline">
                            dilgcarfad@gmail.com
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-slate-700">
            <span class="font-bold text-slate-800">CSC Career Opportunities:</span>
            <a href="http://csc.gov.ph/career" target="_blank" rel="noopener"
                class="ml-2 font-semibold text-blue-700 underline decoration-blue-300 underline-offset-2 transition hover:text-blue-900">
                View Opportunities
            </a>
        </div>
    </section>

    <section class="mx-auto mb-10 py-10 max-w-7xl sm:px-6">
        <div class="flex flex-row gap-5 justify-around text-sm text-gray-500">
            @include('partials.data_privacy_notice')
            @include('partials.privacy_policy')
            @include('partials.about_this_site')
        </div>
    </section>

    @include('partials.loader')
@endsection

@push('styles')
    <style>
        @keyframes aboutFadeUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .about-card,
        .about-hero {
            animation: aboutFadeUp 0.45s ease-out both;
        }

        .about-card:nth-child(2) {
            animation-delay: 0.08s;
        }

        .about-card:nth-child(3) {
            animation-delay: 0.14s;
        }
    </style>
@endpush
