@extends('layout.admin')
@section('title', 'Email Log')

@section('content')
<main class="w-full h-full min-h-0 flex flex-col gap-4 pb-4 overflow-hidden font-montserrat">
    <section class="flex-none flex items-center justify-between max-w-full">
            <button aria-label="Back" onclick="window.location.href='{{ route('admin_activity_log') }}'" class="group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        <div class="flex items-center justify-between w-full border-b border-[#0D2B70] py-2">
            <h1 class="flex items-center gap-3 w-full text-white text-3xl font-montserrat py-2 tracking-wide select-none">
                <span class="whitespace-nowrap text-[#0D2B70]">Email Receipt</span>
            </h1>
        </div>
    </section>

    <section class="flex-none w-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:p-5">
        <div class="grid gap-3 md:grid-cols-2">
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">To</div>
                <div class="text-sm text-slate-800 break-words">{{ $emailLog->recipient_email ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">From</div>
                <div class="text-sm text-slate-800 break-words">
                    {{ $emailLog->from_name ? $emailLog->from_name . ' ' : '' }}{{ $emailLog->from_email ?? 'N/A' }}
                </div>
            </div>
            <div class="md:col-span-2">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Subject</div>
                <div class="text-sm font-semibold text-slate-900 break-words">{{ $emailLog->subject ?? 'N/A' }}</div>
      </div
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Status</div>
                <div class="text-sm text-slate-800">{{ $emailLog->status ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Sent At</div>
                <div class="text-sm text-slate-800">{{ optional($emailLog->sent_at)->format('Y-m-d H:i:s') ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Message ID</div>
                <div class="text-sm text-slate-800 break-words">{{ $emailLog->message_id ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Template</div>
                <div class="text-sm text-slate-800 break-words">{{ $emailLog->template_name ?? 'N/A' }}</div>
            </div>
            @if(!empty($emailLog->error_message))
                <div class="md:col-span-2">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Error</div>
                    <div class="text-sm text-red-700 break-words">{{ $emailLog->error_message }}</div>
                </div>
            @endif
        </div>
    </section>

    <section class="flex-1 min-h-0">
        <div class="h-full flex flex-col min-h-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex-none border-b border-slate-200 px-4 py-3">
                <div class="text-sm font-semibold text-slate-800">Email Preview</div>
                <div class="text-xs text-slate-500">Rendered HTML preview (safe sandbox)</div>
            </div>

            <div class="flex-1 min-h-0">
                <iframe
                    title="Email preview"
                    class="w-full h-full"
                    src="{{ route('admin.email_logs.html', $emailLog) }}"
                    sandbox
                    referrerpolicy="no-referrer"
                ></iframe>
            </div>
        </div>
    </section>

    <!-- <section class="flex-none w-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:p-5">
        <details>
            <summary class="cursor-pointer text-sm font-semibold text-[#0D2B70]">Show plain-text / raw content</summary>
            <div class="mt-3 grid gap-4 md:grid-cols-2">
                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-1">Text</div>
                    <pre class="whitespace-pre-wrap break-words rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-800">{{ $emailLog->body_text ?? 'No text content available.' }}</pre>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-600 mb-1">HTML (raw)</div>
                    <pre class="whitespace-pre-wrap break-words rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-800">{{ $emailLog->body_html ?? 'No HTML content available.' }}</pre>
                </div>
            </div>
        </details>
    </section> -->
</main>
@endsection
