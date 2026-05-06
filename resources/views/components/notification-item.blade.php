@php
    $levels = [
        'info' => 'bg-blue-50 text-blue-600',
        'success' => 'bg-green-50 text-green-600',
        'warning' => 'bg-yellow-50 text-yellow-600',
        'error' => 'bg-red-50 text-red-600',
    ];
    $level = $notification->data['level'] ?? 'info';
    $iconBg = $levels[$level] ?? $levels['info'];

    // Unread styling: subtle blue background
    $containerClass = $notification->read_at ? 'bg-white hover:bg-gray-50' : 'bg-blue-50/30 hover:bg-blue-50/60';
@endphp

<div class="js-notification-item p-4 transition-colors cursor-pointer border-l-4 {{ $notification->read_at ? 'border-transparent' : 'border-blue-600' }} {{ $containerClass }} w-full relative group"
    data-id="{{ $notification->id }}"
    data-link="{{ $notification->data['action_url'] ?? $notification->data['link'] ?? '' }}">

    <div class="flex gap-3">
        <!-- Icon -->
        <div class="mt-0.5 w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ $iconBg }}">
            <i class="w-4 h-4"
                data-feather="{{ $level === 'success' ? 'check' : ($level === 'warning' ? 'alert-triangle' : ($level === 'error' ? 'x' : 'info')) }}"></i>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start gap-2">
                <p class="notif-title text-sm font-bold text-[#0D2B70] truncate pr-2">
                    {{ $notification->data['title'] ?? 'Notification' }}
                </p>
                <span class="notif-time text-[10px] text-gray-400 whitespace-nowrap shrink-0">
                    @php
                        $diff = $notification->created_at->diffForHumans(null, true, true);
                    @endphp
                    {{ $diff === '0s' ? 'Just now' : $diff }}
                </span>
            </div>

            <p class="notif-message text-xs text-slate-600 mt-0.5 line-clamp-2 leading-relaxed">
                {{ $notification->data['message'] ?? '' }}
            </p>
        </div>
    </div>
</div>
