@extends('layout.admin')
@section('title', 'Regional Director')

@section('content')
<main class="w-full h-full min-h-0 flex flex-col gap-4 overflow-hidden font-montserrat">
    <section class="flex-none flex items-center space-x-4 max-w-full">
        <h1 class="flex items-center gap-3 w-full border-b border-[#0D2B70] text-4xl font-montserrat py-2 tracking-wide select-none">
            <span class="whitespace-nowrap text-[#0D2B70]">Regional Director</span>
        </h1>
    </section>

    @if (session('success'))
        <div class="flex-none rounded-xl border border-green-300 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('info'))
        <div class="flex-none rounded-xl border border-blue-300 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700">
            {{ session('info') }}
        </div>
    @endif

    <section class="flex-1 rounded-2xl border border-[#0D2B70] bg-white p-6 shadow-sm">
        @if ($signatory)
            <div class="flex items-start justify-between gap-6">
                <div class="space-y-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Name</p>
                        <p class="mt-1 text-2xl font-bold text-[#0D2B70]">
                            {{ trim($signatory->first_name . ' ' . $signatory->middle_name . ' ' . $signatory->last_name) }}
                        </p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Designation</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $signatory->designation }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Office</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ $signatory->office }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Office Address</p>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $signatory->office_address }}</p>
                    </div>
                </div>

                <div class="shrink-0">
                    <a
                        href="{{ route('signatories.edit', $signatory->id) }}"
                        class="use-loader inline-flex items-center justify-center rounded-xl border border-[#0D2B70] bg-[#0D2B70] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-white hover:text-[#0D2B70]">
                        Edit Regional Director
                    </a>
                </div>
            </div>
        @else
            <div class="flex h-full min-h-[260px] flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 text-center">
                <p class="text-lg font-semibold text-[#0D2B70]">No Regional Director has been configured yet.</p>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    Set up the single signatory record that will be used as the Regional Director in plantilla and COS vacancy forms.
                </p>
                <a
                    href="{{ route('signatories.create') }}"
                    class="use-loader mt-5 inline-flex items-center justify-center rounded-xl border border-[#0D2B70] bg-[#0D2B70] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-white hover:text-[#0D2B70]">
                    Set Up Regional Director
                </a>
            </div>
        @endif
    </section>
</main>
@endsection
