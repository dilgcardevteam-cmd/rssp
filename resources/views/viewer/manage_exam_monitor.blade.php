@extends('layout.admin')
@section('title', 'DILG - Exam Monitor')
@section('content')
<main class="w-full mx-auto flex flex-col space-y-4 overflow-hidden px-4 lg:px-0">
    <section class="flex items-center gap-4 max-w-full border-b border-[#0D2B70] pb-4">
        <button aria-label="Back" onclick="window.location.href='{{ route('admin_exam_management') }}'" class="use-loader group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0D2B70] hover:opacity-80 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <h1 class="text-[#0D2B70] text-2xl md:text-3xl lg:text-4xl font-montserrat">Exam Monitor</h1>
    </section>

    <section class="rounded-xl border border-[#0D2B70] bg-white p-4">
        <p class="text-[#0D2B70] text-lg font-semibold">{{ $vacancy->position_title }}</p>
        <p class="text-[#0D2B70] text-sm mt-1">
            <span class="font-bold">Vacancy ID:</span> {{ $vacancy->vacancy_id }}
            <span class="mx-2">|</span>
            <span class="font-bold">Type:</span> {{ $vacancy->vacancy_type }}
        </p>
        <p class="text-xs text-gray-500 mt-2">Viewer mode is monitoring-only. Checking answers is allowed, scoring remains disabled.</p>
    </section>

    <section class="flex items-center justify-between">
        <span id="monitorLastUpdated" class="text-xs text-gray-500"></span>
        <button id="refreshMonitorBtn"
            onclick="fetchLobbyData(true)"
            class="text-xs bg-white border border-[#0D2B70] text-[#0D2B70] hover:bg-[#0D2B70] hover:text-white px-3 py-1 rounded transition-colors duration-200 flex items-center gap-1">
            <x-heroicon-o-arrow-path class="w-3 h-3" />
            Refresh Now
        </button>
    </section>

    <div class="flex-1 overflow-auto border border-[#0D2B70] rounded-xl bg-white">
        <table class="w-full text-left border-collapse">
            <thead class="bg-[#0D2B70] text-white sticky top-0 z-10">
                <tr>
                    <th class="py-4 px-6 text-left text-sm tracking-wider w-[35%]">Name</th>
                    <th class="py-4 px-6 text-center text-sm tracking-wider w-[20%]">MC</th>
                    <th class="py-4 px-6 text-center text-sm tracking-wider w-[20%]">Essay</th>
                    <th class="py-4 px-6 text-center text-sm tracking-wider w-[15%]">Status</th>
                    <th class="py-4 px-6 text-center text-sm tracking-wider w-[10%]">Action</th>
                </tr>
            </thead>
            <tbody id="exam-lobby-tbody" class="bg-white divide-y divide-gray-200">
                @if (count($participants) > 0)
                    @foreach ($participants as $index => $p)
                        @php
                            $statusColors = [
                                'ready' => '#4ade80',
                                'in-progress' => '#facc15',
                                'submitted' => '#3b82f6',
                                'pending' => '#f75555',
                            ];
                            $status = strtolower($p->status ?? 'pending');
                            $color = $statusColors[$status] ?? '#9ca3af';
                        @endphp
                        <tr class="hover:bg-blue-50 transition-colors duration-200">
                            <td class="py-4 px-6 text-[#0D2B70] font-semibold text-sm">{{ $user_name[$index] ?? 'Unknown User' }}</td>
                            <td class="py-4 px-6 text-center text-[#0D2B70] text-sm">{{ $p->mc_score_str ?? '-' }}</td>
                            <td class="py-4 px-6 text-center text-[#0D2B70] text-sm">{{ $p->essay_score_str ?? '-' }}</td>
                            <td class="py-4 px-6 text-center">
                                <div class="inline-flex items-center gap-2 text-[#0D2B70] font-medium text-sm">
                                    <i class="fa-solid fa-circle text-xs" style="color: {{ $color }}"></i>
                                    <span>{{ $p->status ?? 'Pending' }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <a href="{{ route('admin.view_exam', ['vacancy_id' => $p->vacancy_id, 'user_id' => $p->user_id]) }}" target="_blank"
                                    class="inline-flex items-center gap-1 rounded-md border border-[#0D2B70] px-3 py-1.5 text-xs font-bold text-[#0D2B70] transition-all duration-150 hover:bg-[#002C76] hover:text-white">
                                    <x-heroicon-o-eye class="h-3 w-3" />
                                    <span>View</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-500">
                            <p class="text-lg font-semibold">There are no participants yet.</p>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @include('partials.loader')
</main>

@php
    $examRealtimeConnection = (string) config('broadcasting.default');
    $examRealtimeOptions = (array) data_get(config('broadcasting.connections'), $examRealtimeConnection . '.options', []);
    $examRealtimeKey = (string) data_get(config('broadcasting.connections'), $examRealtimeConnection . '.key', '');
    $examRealtimeEnabled = in_array($examRealtimeConnection, ['reverb', 'pusher'], true) && $examRealtimeKey !== '';
    $examRealtimeConfig = [
        'enabled' => $examRealtimeEnabled,
        'key' => $examRealtimeKey,
        'wsHost' => (string) ($examRealtimeOptions['host'] ?? request()->getHost()),
        'wsPort' => (int) ($examRealtimeOptions['port'] ?? 80),
        'wssPort' => (int) ($examRealtimeOptions['port'] ?? 443),
        'forceTLS' => (bool) ($examRealtimeOptions['useTLS'] ?? request()->isSecure()),
    ];
@endphp
@if ($examRealtimeEnabled)
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
@endif
<script>
    const vacancyId = @json($vacancy->vacancy_id);
    const examRealtimeConfig = @json($examRealtimeConfig);
    let lobbyPollingInterval = null;
    let lobbyFetchInFlight = null;
    let lobbyFetchQueued = false;
    let lobbyFetchTimer = null;
    let realtimeClient = null;
    let realtimeConnected = false;
    const FAST_POLL_MS = 3000;
    const SAFETY_POLL_MS = 15000;

    function updateLastUpdatedTime() {
        const el = document.getElementById('monitorLastUpdated');
        if (!el) return;
        const now = new Date();
        el.textContent = 'Last updated: ' + now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    function updateLobbyTable(participants) {
        const tbody = document.getElementById('exam-lobby-tbody');
        if (!tbody) return;

        if (!participants.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="py-10 text-center text-gray-500">
                        <p class="text-lg font-semibold">There are no participants yet.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = participants.map(p => `
            <tr class="hover:bg-blue-50 transition-colors duration-200">
                <td class="py-4 px-6 text-[#0D2B70] font-semibold text-sm">${p.name}</td>
                <td class="py-4 px-6 text-center text-[#0D2B70] text-sm">${p.mc_score}</td>
                <td class="py-4 px-6 text-center text-[#0D2B70] text-sm">${p.essay_score}</td>
                <td class="py-4 px-6 text-center">
                    <div class="inline-flex items-center gap-2 text-[#0D2B70] font-medium text-sm">
                        <i class="fa-solid fa-circle text-xs" style="color:${p.status_color}"></i>
                        <span class="capitalize">${p.status}</span>
                    </div>
                </td>
                <td class="py-4 px-6 text-center">
                    <a href="/admin/exam_management/${p.vacancy_id}/view_exam/${p.user_id}" target="_blank"
                        class="inline-flex items-center gap-1 rounded-md border border-[#0D2B70] px-3 py-1.5 text-xs font-bold text-[#0D2B70] transition-all duration-150 hover:bg-[#002C76] hover:text-white">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span>View</span>
                    </a>
                </td>
            </tr>
        `).join('');
    }

    function getLobbyPollIntervalMs() {
        return realtimeConnected ? SAFETY_POLL_MS : FAST_POLL_MS;
    }

    function queueLobbyFetch(reason = 'queued', delay = 0, isManual = false) {
        if (lobbyFetchTimer) clearTimeout(lobbyFetchTimer);
        lobbyFetchTimer = setTimeout(() => {
            lobbyFetchTimer = null;
            fetchLobbyData(isManual, reason);
        }, delay);
    }

    function initRealtime() {
        if (!examRealtimeConfig.enabled || typeof window.Pusher === 'undefined') return;

        try {
            realtimeClient = new window.Pusher(examRealtimeConfig.key, {
                wsHost: examRealtimeConfig.wsHost,
                wsPort: examRealtimeConfig.wsPort,
                wssPort: examRealtimeConfig.wssPort,
                forceTLS: !!examRealtimeConfig.forceTLS,
                enabledTransports: ['ws', 'wss'],
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                }
            });

            const monitorChannel = realtimeClient.subscribe(`private-exam-monitor.${vacancyId}`);
            monitorChannel.bind('exam.progress.updated', () => {
                queueLobbyFetch('realtime-event', 80);
            });

            realtimeClient.connection.bind('connected', () => {
                realtimeConnected = true;
                startLobbyPolling();
                queueLobbyFetch('realtime-connected', 0);
            });

            realtimeClient.connection.bind('disconnected', () => {
                realtimeConnected = false;
                startLobbyPolling();
            });

            realtimeClient.connection.bind('unavailable', () => {
                realtimeConnected = false;
                startLobbyPolling();
            });

            realtimeClient.connection.bind('error', () => {
                realtimeConnected = false;
                startLobbyPolling();
            });
        } catch (error) {
            console.error('Realtime init failed:', error);
            realtimeConnected = false;
            startLobbyPolling();
        }
    }

    function fetchLobbyData(isManual = false, reason = 'manual') {
        if (lobbyFetchInFlight) {
            lobbyFetchQueued = true;
            return lobbyFetchInFlight;
        }

        const btn = document.getElementById('refreshMonitorBtn');
        const icon = btn?.querySelector('svg');

        if (isManual && btn) {
            btn.disabled = true;
            icon?.classList.add('animate-spin');
        }

        lobbyFetchInFlight = fetch(`/admin/exam_management/${vacancyId}/lobby-data`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateLobbyTable(data.participants || []);
                updateLastUpdatedTime();
            }
        })
        .catch(error => console.error(`Error fetching lobby data (${reason}):`, error))
        .finally(() => {
            lobbyFetchInFlight = null;
            if (isManual && btn) {
                btn.disabled = false;
                icon?.classList.remove('animate-spin');
            }
            if (lobbyFetchQueued) {
                lobbyFetchQueued = false;
                queueLobbyFetch('queued', 120);
            }
        });

        return lobbyFetchInFlight;
    }

    function startLobbyPolling() {
        if (lobbyPollingInterval) clearInterval(lobbyPollingInterval);
        lobbyPollingInterval = setInterval(() => fetchLobbyData(false, 'poll'), getLobbyPollIntervalMs());
    }

    function stopLobbyPolling() {
        if (lobbyPollingInterval) clearInterval(lobbyPollingInterval);
        lobbyPollingInterval = null;
    }

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopLobbyPolling();
        } else {
            queueLobbyFetch('visibility', 0);
            startLobbyPolling();
        }
    });

    initRealtime();
    startLobbyPolling();
    queueLobbyFetch('initial', 0);
</script>
@endsection
