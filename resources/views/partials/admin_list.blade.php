@php
    $hrDivisionAccessMap = $hrDivisionAccessMap ?? [];
    $hrDivisionAccessLabelMap = $hrDivisionAccessLabelMap ?? [];
    $isSuperadminActor = (auth('admin')->user()->role ?? null) === 'superadmin';
@endphp

@if ($admins->isEmpty())
    <tr data-empty-state="1">
        <td colspan="6" class="px-5 py-12 text-center">
            <div class="mx-auto max-w-md rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6">
                <p class="text-sm font-semibold text-slate-700">No accounts found</p>
                <p class="mt-1 text-sm text-slate-500">Try a different search term or clear the filter.</p>
            </div>
        </td>
    </tr>
@else
    @foreach ($admins as $admin)
        @php
            $roleLabel = match ($admin->role) {
                'superadmin' => 'Superadmin',
                'admin' => 'Admin (HR)',
                'hr_division' => 'HR Division',
                'viewer' => 'Viewer',
                default => ucfirst((string) $admin->role),
            };

            $roleClass = match ($admin->role) {
                'superadmin' => 'border-violet-200 bg-violet-50 text-violet-700',
                'admin' => 'border-blue-200 bg-blue-50 text-blue-700',
                'hr_division' => 'border-amber-200 bg-amber-50 text-amber-700',
                'viewer' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                default => 'border-slate-200 bg-slate-50 text-slate-700',
            };

            $approvalStatus = (string) ($admin->approval_status ?? 'approved');
            $statusKey = match ($approvalStatus) {
                'pending' => 'pending',
                'declined' => 'declined',
                default => ((int) ($admin->is_active ?? 0) === 1 ? 'active' : 'inactive'),
            };

            [$statusLabel, $statusClass] = match ($statusKey) {
                'pending' => ['Pending Approval', 'border-amber-200 bg-amber-50 text-amber-700'],
                'declined' => ['Declined', 'border-rose-200 bg-rose-50 text-rose-700'],
                'active' => ['Active', 'border-emerald-200 bg-emerald-50 text-emerald-700'],
                default => ['Inactive', 'border-slate-300 bg-slate-100 text-slate-600'],
            };

            $isCurrentUser = (int) (auth('admin')->id() ?? 0) === (int) $admin->id;
            $isPending = $statusKey === 'pending';
            $isDeclined = $statusKey === 'declined';
            $displayIdentity = trim((string) ($admin->name ?? '')) ?: ($admin->email ?? ('Admin #' . $admin->id));
            $grantedVacancyIds = array_values($hrDivisionAccessMap[$admin->id] ?? []);
            $grantedAccessLabels = array_values($hrDivisionAccessLabelMap[$admin->id] ?? []);
            $accessPreview = array_slice($grantedAccessLabels, 0, 2);
            $remainingAccessCount = max(count($grantedAccessLabels) - count($accessPreview), 0);
            $canManageHrDivisionAccess = $isSuperadminActor && !$isPending && (($admin->role ?? null) === 'hr_division');
        @endphp

        <tr class="transition hover:bg-slate-50/80" data-row="admin-account" data-role="{{ $admin->role }}" data-status="{{ $statusKey }}">
            <td class="px-5 py-4 align-middle">
                <p class="font-medium text-slate-800">{{ $admin->email }}</p>
                <p class="text-xs text-slate-500">{{ $admin->name }}</p>
                @if ($isCurrentUser)
                    <span class="mt-1 inline-flex rounded-full border border-slate-300 bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                        You
                    </span>
                @endif
            </td>
            <td class="px-5 py-4 align-middle">
                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $roleClass }}">
                    {{ $roleLabel }}
                </span>
            </td>
            <td class="px-5 py-4 align-middle">
                <p class="text-sm font-medium text-slate-700">{{ $admin->office ?: 'Not set' }}</p>
                <p class="text-xs text-slate-500">{{ $admin->designation ?: 'No designation' }}</p>
            </td>
            <td class="px-5 py-4 align-middle">
                @if (($admin->role ?? null) !== 'hr_division')
                    <span class="text-xs font-medium text-slate-400">Not applicable</span>
                @elseif (empty($grantedAccessLabels))
                    <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                        No access assigned
                    </span>
                @else
                    <div class="flex flex-wrap gap-1.5" title="{{ implode(', ', $grantedAccessLabels) }}">
                        @foreach ($accessPreview as $accessLabel)
                            <span class="inline-flex max-w-[220px] truncate rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">
                                {{ $accessLabel }}
                            </span>
                        @endforeach
                        @if ($remainingAccessCount > 0)
                            <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                +{{ $remainingAccessCount }} more
                            </span>
                        @endif
                    </div>
                @endif
            </td>
            <td class="px-5 py-4 text-center align-middle">
                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>
            </td>
            <td class="w-[230px] min-w-[230px] px-5 py-4 text-right align-middle">
                <div class="ml-auto flex w-max flex-nowrap items-center justify-end gap-2 whitespace-nowrap">
                    @if ($canManageHrDivisionAccess)
                        <button
                            type="button"
                            class="js-open-hr-access-modal inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-indigo-300 text-indigo-700 transition hover:bg-indigo-50"
                            title="Manage COS vacancy access"
                            data-admin-id="{{ $admin->id }}"
                            data-admin-name="{{ $displayIdentity }}"
                            data-granted-vacancy-ids='@json($grantedVacancyIds)'
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.008v.008H3.75V6.75zm0 5.25h.008v.008H3.75V12zm0 5.25h.008v.008H3.75v-.008z" />
                            </svg>
                        </button>
                    @endif

                    @if ($isPending)
                        <button
                            type="button"
                            class="js-open-approve-modal inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-emerald-300 text-emerald-700 transition hover:bg-emerald-50"
                            title="Approve account"
                            data-admin-id="{{ $admin->id }}"
                            data-admin-name="{{ $displayIdentity }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </button>
                        <form method="POST" class="js-admin-decline-form no-spinner shrink-0" action="{{ route('admin.decline', $admin->id) }}" data-admin-name="{{ $displayIdentity }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-rose-300 text-rose-600 transition hover:bg-rose-50"
                                title="Decline account"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </button>
                        </form>
                    @elseif ($isDeclined)
                        <button type="button" disabled
                            class="inline-flex h-9 w-9 shrink-0 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 text-slate-300"
                            title="Declined accounts cannot be activated without a new registration">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M15 21H9a6 6 0 0 1-6-6V9a6 6 0 0 1 6-6h6a6 6 0 0 1 6 6v6a6 6 0 0 1-6 6Z" />
                            </svg>
                        </button>
                    @elseif ($isCurrentUser)
                        <button type="button" disabled
                            class="inline-flex h-9 w-9 shrink-0 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 text-slate-300"
                            title="You cannot change your own activation status">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 12h4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </button>
                    @else
                        <form method="POST"
                            class="js-admin-status-form no-spinner shrink-0"
                            data-action="{{ $admin->is_active ? 'deactivate' : 'activate' }}"
                            data-admin-name="{{ $displayIdentity }}"
                            action="{{ route($admin->is_active ? 'admin.deactivate' : 'admin.activate', $admin->id) }}">
                            @csrf
                            <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border text-sm transition {{ $admin->is_active ? 'border-rose-300 text-rose-600 hover:bg-rose-50' : 'border-emerald-300 text-emerald-600 hover:bg-emerald-50' }}"
                                title="{{ $admin->is_active ? 'Deactivate account' : 'Activate account' }}">
                                @if ($admin->is_active)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m18 6-12 12m0-12 12 12" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 12 4.5 4.5L18.75 7.5" />
                                    </svg>
                                @endif
                            </button>
                        </form>
                    @endif

                    @if (!$isPending)
                        @include('partials.admin_edit_account', ['admin' => $admin])
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
@endif
