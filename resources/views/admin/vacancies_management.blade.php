@extends('layout.admin')
@section('title', 'DILG - Job Vacancies Management')
@section('content')
@php
    $isHrDivisionUser = $isHrDivisionUser ?? ((Auth::guard('admin')->user()->role ?? '') === 'hr_division');
    $placeOptions = [
        'DILG-CAR',
        'DILG-CAR Regional Office',
        'Apayao Provincial Office',
        'Abra Provincial Office',
        'Mountain Province Provincial Office',
        'Ifugao Provincial Office',
        'Kalinga Provincial Office',
        'Benguet Provincial Office',
        'Baguio City Office',
    ];
@endphp
@include('partials.loader')
<!-- max-w-7xl -->
<!-- test -->
<main class="w-full h-full min-h-0 flex flex-col space-y-4 pb-4 overflow-hidden">

    <!-- Header Section -->
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-white text-4xl font-montserrat py-2 tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Vacancies Management</span>
        </h1>
    </section>

    <!-- Search and Controls -->
    <section x-data="{ filtersOpen: true, actionsMenuOpen: false }" class="flex-none mt-1 w-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <h2 class="text-[10px] font-bold uppercase tracking-wide text-[#0D2B70]">Filters</h2>
                <div id="filterLoadingIndicator" class="hidden items-center gap-2 rounded-full bg-[#0D2B70]/8 px-3 py-1 text-[11px] font-semibold text-[#0D2B70]">
                    <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-90" d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                    </svg>
                    <span>Applying filters...</span>
                </div>
                <p id="filterLoadingStatus" class="sr-only" aria-live="polite"></p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="filtersOpen = !filtersOpen"
                    :aria-expanded="filtersOpen.toString()"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-[#0D2B70] hover:text-[#173f96]"
                >
                    <span x-text="filtersOpen ? 'Hide Filters' : 'Show Filters'"></span>
                    <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': filtersOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                @unless($isHrDivisionUser)
                <div class="relative" @click.away="actionsMenuOpen = false">
                    <button
                        type="button"
                        @click="actionsMenuOpen = !actionsMenuOpen"
                        :aria-expanded="actionsMenuOpen.toString()"
                        class="inline-flex items-center justify-center rounded-lg border border-[#0D2B70]/20 p-2 text-[#0D2B70] transition hover:bg-[#0D2B70] hover:text-white"
                        aria-label="Open actions menu"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div
                        x-show="actionsMenuOpen"
                        x-cloak
                        x-transition
                        class="absolute right-0 z-50 mt-2 w-[min(18rem,calc(100vw-2rem))] rounded-xl border border-slate-200 bg-white p-3 shadow-lg"
                    >
                        <div class="grid gap-2">
                            <div x-data="{ open: false }" class="relative min-w-0">
                                <button
                                    type="button"
                                    @click="open = !open"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#0D2B70] bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm transition hover:bg-[#0D2B70] hover:text-white"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v10m0 0l-4-4m4 4l4-4M5 18h14" />
                                    </svg>
                                    Download
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div
                                    x-show="open"
                                    x-cloak
                                    @click.away="open = false"
                                    x-transition
                                    class="absolute left-0 z-50 mt-2 w-full min-w-[220px] rounded-xl border border-gray-200 bg-white shadow-lg"
                                >
                                    <a href="{{ route('downloadCOSTemplate') }}" class="block px-4 py-2 text-sm font-semibold text-[#0D2B70] hover:bg-gray-100">
                                        Download COS template
                                    </a>
                                    <a href="{{ route('downloadPlantillaTemplate') }}" class="block px-4 py-2 text-sm font-semibold text-[#0D2B70] hover:bg-gray-100">
                                        Download Plantilla template
                                    </a>
                                </div>
                            </div>

                            <div x-data="{ open: false }" class="relative min-w-0">
                                <button
                                    type="button"
                                    @click="open = !open"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#0D2B70] bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm transition hover:bg-[#0D2B70] hover:text-white"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                    </svg>
                                    Export
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div
                                    x-show="open"
                                    x-cloak
                                    @click.away="open = false"
                                    x-transition
                                    class="absolute left-0 z-50 mt-2 w-full min-w-[220px] rounded-xl border border-gray-200 bg-white shadow-lg"
                                >
                                    @include('partials.alerts_template', [
                                        'id' => 'exportCOS',
                                        'showTrigger' => true,
                                        'triggerText' => 'Export COS vacancies',
                                        'triggerClass' => 'block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold text-left',
                                        'title' => 'Export Confirmation',
                                        'message' => 'Are you sure you want to export COS vacancies?',
                                        'showCancel' => true,
                                        'cancelText' => 'No, Cancel',
                                        'okText' => 'Yes, Export',
                                        'okAction' => "window.location.href='" . route('exportJobVacancyCOS') . "'",
                                    ])
                                    @include('partials.alerts_template', [
                                        'id' => 'exportPlantilla',
                                        'showTrigger' => true,
                                        'triggerText' => 'Export Plantilla vacancies',
                                        'triggerClass' => 'block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold text-left',
                                        'title' => 'Export Confirmation',
                                        'message' => 'Are you sure you want to export Plantilla vacancies?',
                                        'showCancel' => true,
                                        'cancelText' => 'No, Cancel',
                                        'okText' => 'Yes, Export',
                                        'okAction' => "window.location.href='" . route('exportJobVacancyPlantilla') . "'",
                                    ])
                                    @include('partials.alerts_template', [
                                        'id' => 'exportAll',
                                        'showTrigger' => true,
                                        'triggerText' => 'Export All vacancies',
                                        'triggerClass' => 'block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold text-left',
                                        'title' => 'Export Confirmation',
                                        'message' => 'Are you sure you want to export All vacancies?',
                                        'showCancel' => true,
                                        'cancelText' => 'No, Cancel',
                                        'okText' => 'Yes, Export',
                                        'okAction' => "window.location.href='" . route('exportJobVacancyAll') . "'",
                                    ])
                                </div>
                            </div>

                            <div x-data="{ open: false }" class="relative min-w-0">
                                <button
                                    type="button"
                                    @click="open = !open"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#0D2B70] bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm transition hover:bg-[#0D2B70] hover:text-white"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M12 12v8m-4-4l4 4 4-4"></path>
                                    </svg>
                                    Import
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div
                                    x-show="open"
                                    x-cloak
                                    @click.away="open = false"
                                    x-transition
                                    class="absolute left-0 z-50 mt-2 w-full min-w-[220px] rounded-xl border border-gray-200 bg-white shadow-lg"
                                >
                                    @include('partials.alerts_template', [
                                        'id' => 'importCOS',
                                        'showTrigger' => true,
                                        'triggerText' => 'Import COS vacancies',
                                        'triggerClass' => 'block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold text-left',
                                        'title' => 'Import Confirmation',
                                        'message' => 'Upload your CSV file to import COS vacancies.',
                                        'showCancel' => true,
                                        'cancelText' => 'Cancel',
                                        'okText' => null,
                                        'content' =>
                                        '<form action="' . route('importJobVacancyCOS') . '" method="POST" enctype="multipart/form-data" class="flex flex-col items-center gap-3 w-full">
                                            ' . csrf_field() . '
                                            <input type="file" name="import_file" accept=".csv" required class="border border-gray-300 rounded px-2 py-1 w-full">
                                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-full font-semibold transition">
                                                Import COS Vacancies
                                            </button>
                                        </form>',
                                    ])
                                    @include('partials.alerts_template', [
                                        'id' => 'importPlantilla',
                                        'showTrigger' => true,
                                        'triggerText' => 'Import Plantilla vacancies',
                                        'triggerClass' => 'block px-4 py-2 text-sm text-[#0D2B70] hover:bg-gray-100 font-semibold text-left',
                                        'title' => 'Import Confirmation',
                                        'message' => 'Upload your CSV file to import Plantilla vacancies.',
                                        'showCancel' => true,
                                        'cancelText' => 'Cancel',
                                        'okText' => null,
                                        'content' =>
                                        '<form action="' . route('importJobVacancyPlantilla') . '" method="POST" enctype="multipart/form-data" class="flex flex-col items-center gap-3 w-full">
                                            ' . csrf_field() . '
                                            <input type="file" name="import_file" accept=".csv" required class="border border-gray-300 rounded px-2 py-1 w-full">
                                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-full font-semibold transition">
                                                Import Plantilla Vacancies
                                            </button>
                                        </form>',
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endunless
            </div>
        </div>

        <div x-show="filtersOpen" x-transition class="mt-4">
            <div class="flex w-full flex-col gap-4">
                <form class="relative w-full" onsubmit="return false;">
                    <label for="searchInput" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Search</label>
                    <input
                        id="searchInput"
                        type="search"
                        placeholder="Search by vacancy no. or job title"
                        aria-label="Search"
                        value="{{ session('vacancyFilterSearch') }}"
                        class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#0D2B70] focus:ring-2 focus:ring-[#0D2B70]/20"
                        oninput="fetchVacanciesDebounced()"
                    />
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="pointer-events-none absolute left-3 top-[39px] h-5 w-5 -translate-y-1/2 text-slate-400"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                </form>

                <div class="flex flex-col gap-3 xl:flex-row xl:items-stretch">
                    <div class="grid flex-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">
                        <!-- Job Type Filter -->
                        <div x-data="{ jobOpen: false }" data-filter-dropdown="job" class="relative min-w-0">
                            <button
                                type="button"
                                @click="jobOpen = !jobOpen"
                                class="inline-flex w-full items-center justify-between gap-2 rounded-xl border border-[#0D2B70] bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm transition hover:bg-[#0D2B70] hover:text-white"
                            >
                                <span class="flex min-w-0 items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span class="truncate" x-text="$el.closest('.relative').querySelector('#jobFilter option:checked')?.text || 'Job Type'"></span>
                                </span>
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <select id="jobFilter" class="hidden">
                                @if($isHrDivisionUser)
                                    <option value="COS" selected>Contract of Service</option>
                                @else
                                    <option value="" {{ session('vacancyFilterJob') == '' ? 'selected' : '' }}>All</option>
                                    <option value="COS" {{ session('vacancyFilterJob') == 'COS' ? 'selected' : '' }}>Contract of Service</option>
                                    <option value="Plantilla" {{ session('vacancyFilterJob') == 'Plantilla' ? 'selected' : '' }}>PLANTILLA</option>
                                @endif
                            </select>

                            <div
                                x-show="jobOpen"
                                x-cloak
                                @click.away="jobOpen = false"
                                x-transition
                                class="absolute left-0 z-50 mt-2 w-full min-w-[190px] rounded-xl border border-gray-200 bg-white shadow-lg sm:left-auto sm:right-0"
                            >
                                @if($isHrDivisionUser)
                                    <button
                                        onclick="document.getElementById('jobFilter').value = 'COS'; document.getElementById('jobFilter').dispatchEvent(new Event('change')); fetchVacancies();"
                                        @click="jobOpen = false; $el.closest('.relative').querySelector('button span span').innerText = 'Contract of Service'"
                                        class="block w-full bg-gray-100 px-4 py-2 text-left text-sm font-semibold text-[#0D2B70] hover:bg-gray-100"
                                    >
                                        Contract of Service
                                    </button>
                                @else
                                    <button
                                        onclick="document.getElementById('jobFilter').value = ''; document.getElementById('jobFilter').dispatchEvent(new Event('change')); fetchVacancies();"
                                        @click="jobOpen = false; $el.closest('.relative').querySelector('button span span').innerText = 'All'"
                                        class="block w-full px-4 py-2 text-left text-sm font-semibold text-[#0D2B70] hover:bg-gray-100 {{ session('vacancyFilterJob') == '' ? 'bg-gray-100' : '' }}"
                                    >
                                        All
                                    </button>
                                    <button
                                        onclick="document.getElementById('jobFilter').value = 'COS'; document.getElementById('jobFilter').dispatchEvent(new Event('change')); fetchVacancies();"
                                        @click="jobOpen = false; $el.closest('.relative').querySelector('button span span').innerText = 'Contract of Service'"
                                        class="block w-full px-4 py-2 text-left text-sm font-semibold text-[#0D2B70] hover:bg-gray-100 {{ session('vacancyFilterJob') == 'COS' ? 'bg-gray-100' : '' }}"
                                    >
                                        Contract of Service
                                    </button>
                                    <button
                                        onclick="document.getElementById('jobFilter').value = 'Plantilla'; document.getElementById('jobFilter').dispatchEvent(new Event('change')); fetchVacancies();"
                                        @click="jobOpen = false; $el.closest('.relative').querySelector('button span span').innerText = 'PLANTILLA'"
                                        class="block w-full px-4 py-2 text-left text-sm font-semibold text-[#0D2B70] hover:bg-gray-100 {{ session('vacancyFilterJob') == 'Plantilla' ? 'bg-gray-100' : '' }}"
                                    >
                                        PLANTILLA
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div x-data="{ statusOpen: false, selectedStatus: '{{ session('vacancyFilterStatus') ?: 'All' }}' }" data-filter-dropdown="status" class="relative min-w-0">
                            <button
                                type="button"
                                @click="statusOpen = !statusOpen"
                                class="inline-flex w-full items-center justify-between gap-2 rounded-xl border border-[#0D2B70] bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm transition hover:bg-[#0D2B70] hover:text-white"
                            >
                                <span class="flex min-w-0 items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                    <span class="truncate" x-text="selectedStatus"></span>
                                </span>
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <select id="statusFilter" class="hidden">
                                <option value="" {{ session('vacancyFilterStatus') == '' ? 'selected' : '' }}>All</option>
                                <option value="OPEN" {{ session('vacancyFilterStatus') == 'OPEN' ? 'selected' : '' }}>OPEN</option>
                                <option value="CLOSED" {{ session('vacancyFilterStatus') == 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
                            </select>

                            <div
                                x-show="statusOpen"
                                x-cloak
                                @click.away="statusOpen = false"
                                x-transition
                                class="absolute left-0 z-50 mt-2 w-full min-w-[190px] rounded-xl border border-gray-200 bg-white shadow-lg sm:left-auto sm:right-0"
                            >
                                <button
                                    onclick="document.getElementById('statusFilter').value = ''; document.getElementById('statusFilter').dispatchEvent(new Event('change')); fetchVacancies();"
                                    @click="statusOpen = false; selectedStatus = 'All'"
                                    class="block w-full px-4 py-2 text-left text-sm font-semibold text-[#0D2B70] hover:bg-gray-100"
                                    :class="{ 'bg-gray-100': selectedStatus === 'All' }"
                                >
                                    All
                                </button>
                                <button
                                    onclick="document.getElementById('statusFilter').value = 'OPEN'; document.getElementById('statusFilter').dispatchEvent(new Event('change')); fetchVacancies();"
                                    @click="statusOpen = false; selectedStatus = 'OPEN'"
                                    class="block w-full px-4 py-2 text-left text-sm font-semibold text-[#0D2B70] hover:bg-gray-100"
                                    :class="{ 'bg-gray-100': selectedStatus === 'OPEN' }"
                                >
                                    OPEN
                                </button>
                                <button
                                    onclick="document.getElementById('statusFilter').value = 'CLOSED'; document.getElementById('statusFilter').dispatchEvent(new Event('change')); fetchVacancies();"
                                    @click="statusOpen = false; selectedStatus = 'CLOSED'"
                                    class="block w-full px-4 py-2 text-left text-sm font-semibold text-[#0D2B70] hover:bg-gray-100"
                                    :class="{ 'bg-gray-100': selectedStatus === 'CLOSED' }"
                                >
                                    CLOSED
                                </button>
                            </div>
                        </div>

                        <!-- Place Filter -->
                        <div x-data="{ placeOpen: false, selectedPlace: '{{ session('vacancyFilterPlace') ?: 'All Places' }}' }" data-filter-dropdown="place" class="relative min-w-0">
                            <button
                                type="button"
                                @click="placeOpen = !placeOpen"
                                class="inline-flex w-full items-center justify-between gap-2 rounded-xl border border-[#0D2B70] bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm transition hover:bg-[#0D2B70] hover:text-white"
                            >
                                <span class="flex min-w-0 items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="truncate" x-text="selectedPlace"></span>
                                </span>
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <select id="placeFilter" class="hidden">
                                <option value="" {{ session('vacancyFilterPlace') == '' ? 'selected' : '' }}>All Places</option>
                                @foreach ($placeOptions as $placeOption)
                                    <option value="{{ $placeOption }}" {{ session('vacancyFilterPlace') == $placeOption ? 'selected' : '' }}>{{ $placeOption }}</option>
                                @endforeach
                            </select>

                            <div
                                x-show="placeOpen"
                                x-cloak
                                @click.away="placeOpen = false"
                                x-transition
                                class="absolute left-0 z-50 mt-2 w-full min-w-[220px] rounded-xl border border-gray-200 bg-white shadow-lg sm:left-auto sm:right-0"
                            >
                                <button
                                    onclick="document.getElementById('placeFilter').value = ''; document.getElementById('placeFilter').dispatchEvent(new Event('change')); fetchVacancies();"
                                    @click="placeOpen = false; selectedPlace = 'All Places'"
                                    class="block w-full px-4 py-2 text-left text-sm font-semibold text-[#0D2B70] hover:bg-gray-100"
                                    :class="{ 'bg-gray-100': selectedPlace === 'All Places' }"
                                >
                                    All Places
                                </button>
                                @foreach ($placeOptions as $placeOption)
                                    <button
                                        onclick="document.getElementById('placeFilter').value = '{{ $placeOption }}'; document.getElementById('placeFilter').dispatchEvent(new Event('change')); fetchVacancies();"
                                        @click="placeOpen = false; selectedPlace = '{{ $placeOption }}'"
                                        class="block w-full px-4 py-2 text-left text-sm font-semibold text-[#0D2B70] hover:bg-gray-100"
                                        :class="{ 'bg-gray-100': selectedPlace === '{{ $placeOption }}' }"
                                    >
                                        {{ $placeOption }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="hidden xl:block w-px self-stretch bg-slate-200"></div>

                    <!-- Add New Vacancy Button -->
                    <div class="xl:w-[220px] xl:pl-1">
                        <div x-data="{ open: false }" class="relative min-w-0">
                            <button
                                type="button"
                                @click="open = !open"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#0D2B70] bg-white px-4 py-2.5 text-sm font-semibold text-[#0D2B70] shadow-sm transition hover:bg-[#0D2B70] hover:text-white"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                                </svg>
                                New Vacancy
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div
                                x-show="open"
                                x-cloak
                                @click.away="open = false"
                                x-transition
                                class="absolute left-0 z-50 mt-2 w-full min-w-[190px] rounded-xl border border-gray-200 bg-white shadow-lg sm:left-auto sm:right-0"
                            >
                                <a href="{{ route('vacancies.addcos') }}" class="use-loader block px-4 py-2 text-sm font-semibold text-[#0D2B70] hover:bg-gray-100">
                                    Add COS Vacancy
                                </a>
                                @unless($isHrDivisionUser)
                                    <a href="{{ route('vacancies.addplantilla') }}" class="use-loader block px-4 py-2 text-sm font-semibold text-[#0D2B70] hover:bg-gray-100">
                                        Add Plantilla Vacancy
                                    </a>
                                @endunless
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if(session('success'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Success!</strong>
        <p class="text-sm">{{ session('success') }}</p>
    </div>
    @endif

    @if ($errors->any())
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed top-5 right-5 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
    >
        <strong class="font-bold">Failed!</strong>
        <ul class="list-disc list-inside text-sm mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if (session('error'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 3000)"
            x-show="show"
            x-transition
            class="fixed top-5 right-5 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl shadow-lg w-full max-w-sm"
        >
            <strong class="font-bold">Failed!</strong>
                    {{ session('error') }}
        </div>
    @endif

    <!-- Table Container -->
    <div class="flex-1 flex flex-col min-h-0 max-h-[calc(98vh-12rem)] overflow-hidden border border-[#0D2B70] rounded-xl">
        <!-- Table Header -->
        <div class="bg-[#0D2B70] text-white text-left rounded-t-xl">
            <table class="w-full border-collapse table-fixed">
                <thead class="bg-[#0D2B70] text-white sticky top-0 z-10"> 
                    <tr>
                        <th class="px-3 py-2 text-[11px] font-semibold text-center w-[10%]">Vacancy Number</th>
                        <th class="px-3 py-2 text-[11px] font-semibold text-center w-[25%]">Job Title</th>
                        <th class="px-3 py-2 text-[11px] font-semibold text-center w-[15%]">Monthly Salary</th>
                        <th class="px-3 py-2 text-[11px] font-semibold text-center w-[15%]">Closing Date</th>
                        <th class="px-3 py-2 text-[11px] font-semibold text-center w-[25%]">Place of Assignment</th>
                        <th class="px-3 py-2 text-[11px] font-semibold text-center w-[10%]">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="flex-1 overflow-auto min-h-0">
            <table class="w-full text-left border-collapse table-fixed">
                <tbody id="vacancy-list" class="divide-y divide-[#0D2B70]">
                    @forelse ($vacancies as $vacancy)
                        @include('partials.admin_job_vacancy_card', ['vacancy' => $vacancy, 'index' => $loop->index])
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-sm text-gray-500">
                                <i data-feather="info" class="mr-2 inline-block h-4 w-4 text-gray-400 font-montserrat"></i>
                                No Job Vacancy
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
<script>
    // Re-initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
</script>
</main>

@push('scripts')
<script>
    const loader_ = document.getElementById('loader');
    const loaderText_ = document.getElementById('loader-text');
    const loaderLive_ = document.getElementById('loader-live');
    const filterLoadingIndicator = document.getElementById('filterLoadingIndicator');
    const filterLoadingStatus = document.getElementById('filterLoadingStatus');

    function setFilterLoadingState(isLoading) {
        filterLoadingIndicator?.classList.toggle('hidden', !isLoading);
        filterLoadingIndicator?.classList.toggle('inline-flex', isLoading);

        if (filterLoadingStatus) {
            filterLoadingStatus.textContent = isLoading ? 'Applying filters...' : 'Filters applied.';
        }

        if (loader_) {
            loader_.classList.toggle('hidden', !isLoading);
            loader_.setAttribute('aria-busy', isLoading ? 'true' : 'false');
        }

        if (loaderText_) {
            loaderText_.textContent = isLoading ? 'Applying filters...' : 'Loading...';
        }

        if (loaderLive_) {
            loaderLive_.textContent = isLoading ? 'Applying filters...' : 'Ready';
        }
    }

    function attachLoaderListeners() {
        document.querySelectorAll('.use-loader').forEach(button => {
            button.addEventListener('click', () => {
                loader_?.classList.remove('hidden');
            });
        });

        document.querySelectorAll('form .use-loader').forEach(button => {
            button.closest('form')?.addEventListener('submit', () => {
                loader_?.classList.remove('hidden');
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const alpineProbe = document.querySelector('[x-data]');
        const alpineOperational = Boolean(
            window.Alpine && alpineProbe && (alpineProbe._x_dataStack || alpineProbe.__x)
        );

        if (!alpineOperational) {
            document.querySelectorAll('[data-filter-dropdown]').forEach((wrapper) => {
                const triggerBtn = wrapper.querySelector('button[type="button"]');
                const select = wrapper.querySelector('select');
                const menu = wrapper.querySelector('div[x-show]');

                if (triggerBtn) {
                    triggerBtn.classList.add('hidden');
                }

                if (menu) {
                    menu.classList.add('hidden');
                }

                if (select) {
                    select.classList.remove('hidden');
                    select.classList.add(
                        'w-full',
                        'rounded-xl',
                        'border',
                        'border-[#0D2B70]',
                        'bg-white',
                        'px-3',
                        'py-2.5',
                        'text-sm',
                        'font-semibold',
                        'text-[#0D2B70]',
                        'shadow-sm'
                    );
                }
            });
        }

        attachLoaderListeners();
        fetchVacancies();
    });

    window.attachLoaderListeners = attachLoaderListeners;

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    const fetchVacanciesDebounced = debounce(fetchVacancies, 400);

    async function fetchVacancies() {
        const status = document.getElementById('statusFilter')?.value ?? '';
        const sort = document.getElementById('sortFilter')?.value ?? '';
        const job = document.getElementById('jobFilter')?.value ?? '';
        const place = encodeURIComponent(document.getElementById('placeFilter')?.value ?? '');
        const search = encodeURIComponent(document.getElementById('searchInput')?.value ?? '');

        try {
            setFilterLoadingState(true);
            const res = await fetch(`/admin/vacancies_management/filter?status=${status}&sort=${sort}&job=${job}&place=${place}&search=${search}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) {
                const body = await res.text();
                console.error('Vacancies load failed:', res.status, body);
                throw new Error(`Server responded with ${res.status}`);
            }

            const html = await res.text();
            document.getElementById('vacancy-list').innerHTML = html;
            attachLoaderListeners();
        } catch (err) {
            console.error('Fetch vacancies error:', err);
            showAppToast('Failed to load vacancies. See console for details.');
        } finally {
            setFilterLoadingState(false);
        }
    }

    document.getElementById('statusFilter')?.addEventListener('change', fetchVacancies);
    document.getElementById('jobFilter')?.addEventListener('change', fetchVacancies);
    document.getElementById('placeFilter')?.addEventListener('change', fetchVacancies);
    document.getElementById('sortFilter')?.addEventListener('change', fetchVacancies);
</script>
@endpush
@endsection
