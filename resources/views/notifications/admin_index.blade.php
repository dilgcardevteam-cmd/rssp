@extends('layout.admin')
@section('title', 'Notification History')
@section('content')
<div class="max-w-4xl mx-auto py-6 px-4 font-montserrat">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#0D2B70]">Notification History</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ $unreadCount > 0 ? $unreadCount . ' unread notification' . ($unreadCount > 1 ? 's' : '') : 'All caught up!' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.mark_all') }}">
                @csrf
                <button type="submit"
                    class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                    Mark all as read
                </button>
            </form>
            @endif
            <a href="{{ route('dashboard_admin', [], false) }}"
                class="text-sm font-semibold text-[#0D2B70] hover:underline">&larr; Back</a>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex gap-2 mb-5">
        <a href="{{ route('admin.notifications.index', [], false) }}"
            class="px-4 py-1.5 rounded-full text-xs font-semibold border transition-colors
                   {{ $filter === 'all' ? 'bg-[#0D2B70] text-white border-[#0D2B70]' : 'bg-white text-slate-600 border-gray-300 hover:border-[#0D2B70]' }}">
            All
        </a>
        <a href="{{ route('admin.notifications.index', ['filter' => 'unread'], false) }}"
            class="px-4 py-1.5 rounded-full text-xs font-semibold border transition-colors
                   {{ $filter === 'unread' ? 'bg-[#0D2B70] text-white border-[#0D2B70]' : 'bg-white text-slate-600 border-gray-300 hover:border-[#0D2B70]' }}">
            Unread
            @if($unreadCount > 0)
                <span class="ml-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] font-bold">
                    {{ $unreadCount >= 100 ? '99+' : $unreadCount }}
                </span>
            @endif
        </a>
    </div>

    {{-- List --}}
    <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
        <ul class="divide-y divide-gray-100">
            @forelse($notifications as $notification)
                @include('components.notification-item', ['notification' => $notification])
            @empty
                <li class="text-center text-sm text-slate-500 py-12">
                    @if($filter === 'unread')
                        No unread notifications.
                    @else
                        No notifications yet.
                    @endif
                </li>
            @endforelse
        </ul>
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const items = document.querySelectorAll('.js-notification-item');
        const normalizeNotificationUrl = (targetUrl) => {
            if (!targetUrl) return '';
            try {
                const parsed = new URL(targetUrl, window.location.origin);
                if (parsed.origin !== window.location.origin) {
                    return `${parsed.pathname}${parsed.search}${parsed.hash}`;
                }
                return parsed.href;
            } catch (_) {
                return targetUrl;
            }
        };

        items.forEach((item) => {
            item.addEventListener('click', async () => {
                const id = item.dataset.id;
                const link = item.dataset.link;

                if (id) {
                    try {
                        await fetch(`/notifications/${id}/read`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token || '' },
                            keepalive: true
                        });
                    } catch (e) {}
                }

                if (link) {
                    window.location.href = normalizeNotificationUrl(link);
                }
            });
        });

        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endpush
