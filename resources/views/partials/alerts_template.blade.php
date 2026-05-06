@props([
    'id' => 'alertModal',
    'showTrigger' => true,
    'triggerText' => 'Open',
    'triggerClass' => 'bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold transition',
    'title' => 'Alert',
    'message' => 'Are you sure?',
    'showCancel' => true,
    'cancelText' => 'Cancel',
    'okText' => 'OK',
    'okAction' => '',
    'content' => '',
])

@php
    $modalId = strtolower(preg_replace('/[^A-Za-z0-9_-]/', '', (string) $id) ?: 'alertModal');
    $openEvent = 'open-confirm-' . $modalId;
    $confirmEvent = 'confirm-' . $modalId;
    $hasCustomContent = !empty(trim((string) $content));
@endphp

@if(!$hasCustomContent)
    <div
        x-data
        @if(!$showTrigger)
            x-init="window.dispatchEvent(new CustomEvent(@js($openEvent)))"
        @endif
    >
        @if ($showTrigger)
            <button
                type="button"
                @click="window.dispatchEvent(new CustomEvent(@js($openEvent)))"
                class="{{ $triggerClass }}">
                {{ $triggerText }}
            </button>
        @endif

        <x-confirm-modal
            :title="$title"
            :message="$message"
            :event="$openEvent"
            :confirm="$confirmEvent"
            :confirmText="$okText"
            :cancelText="$cancelText"
        />
    </div>

        @if(!empty(trim((string) $okAction)))
        <script>
            window.addEventListener(@json($confirmEvent), function () {
                {!! $okAction !!}
            });
        </script>
    @endif
@else
    <div x-data="{ showModal: {{ $showTrigger ? 'false' : 'true' }} }">
        @if ($showTrigger)
            <button
                type="button"
                @click="showModal = true"
                class="{{ $triggerClass }}">
                {{ $triggerText }}
            </button>
        @endif

        <template x-teleport="body">
            <div
                x-show="showModal"
                x-cloak
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[10000] overflow-y-auto bg-slate-900/60 backdrop-blur-md"
                style="display: none;"
                @keydown.escape.window="showModal = false"
            >
                <div class="absolute inset-0" @click="showModal = false" aria-hidden="true"></div>

                <div class="relative flex min-h-full w-full items-center justify-center px-4 py-6">
                    <div
                        class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-2 scale-[0.98]"
                    >
                    <button
                        type="button"
                        @click="showModal = false"
                        class="absolute right-3 top-3 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        aria-label="Close"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-base font-bold text-slate-900">{{ $title }}</h2>
                    </div>

                    <div class="px-5 py-4">
                        <p class="mb-4 text-sm leading-relaxed text-slate-700">{!! $message !!}</p>

                        @if ($showCancel || !empty($okText))
                            <div class="flex justify-end gap-2 pb-3">
                                @if ($showCancel)
                                    <button
                                        type="button"
                                        @click="showModal = false"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                                    >
                                        {{ $cancelText }}
                                    </button>
                                @endif

                                @if (!$content)
                                    <button
                                        type="button"
                                        @click="showModal = false; {{ $okAction }};"
                                        class="inline-flex items-center justify-center rounded-lg bg-[#0D2B70] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0A2259]"
                                    >
                                        {{ $okText }}
                                    </button>
                                @endif
                            </div>
                        @endif

                        {!! $content !!}
                    </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endif
