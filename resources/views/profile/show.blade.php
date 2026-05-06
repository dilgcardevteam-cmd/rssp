@extends('layout.app')
@section('title', 'My Profile')
@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-6">
    <h1 class="text-2xl font-bold mb-4">Profile</h1>
    <div class="flex items-center gap-4 mb-6">
        @php
            $avatar = $user->avatar_path ? asset('storage/'.$user->avatar_path) : null;
            $middleInitial = filled($user->middle_name) ? mb_substr(trim($user->middle_name), 0, 1) . '.' : '';
            $displayName = trim(implode(' ', array_filter([
                trim($user->first_name ?? ''),
                $middleInitial,
                trim($user->last_name ?? ''),
            ], fn ($part) => $part !== '')));
            $displayName = $displayName !== '' ? $displayName : 'N/A';
            $initials = collect(explode(' ', $displayName))->map(fn($p)=>mb_substr($p,0,1))->join('');
        @endphp
        @if($avatar)
            <img src="{{ $avatar }}" alt="Avatar" class="w-16 h-16 rounded-full object-cover">
        @else
            <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg font-bold">{{ $initials }}</div>
        @endif
        <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="flex items-center gap-2">
            @csrf
            <input type="file" name="avatar" accept="image/png,image/jpeg" class="text-sm">
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Upload</button>
        </form>
    </div>
    @if(session('status'))
        <div class="p-3 bg-green-50 text-green-700 rounded mb-3">{{ session('status') }}</div>
    @endif
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <div class="text-sm text-gray-500">Name</div>
            <div class="font-semibold">{{ $displayName }}</div>
        </div>
        <div>
            <div class="text-sm text-gray-500">Email</div>
            <div class="font-semibold">{{ $user->email }}</div>
        </div>
        @php $bio = optional($user->profile)->bio ?? $user->bio; @endphp
        @if($bio)
        <div class="sm:col-span-2">
            <div class="text-sm text-gray-500">Bio</div>
            <div class="font-semibold">{{ $bio }}</div>
        </div>
        @endif
        @if(optional($user->profile)->phone)
        <div>
            <div class="text-sm text-gray-500">Phone</div>
            <div class="font-semibold">{{ $user->profile->phone }}</div>
        </div>
        @endif
        @if(optional($user->profile)->address)
        <div>
            <div class="text-sm text-gray-500">Address</div>
            <div class="font-semibold">{{ $user->profile->address }}</div>
        </div>
        @endif
    </div>
    <div class="mt-6 flex gap-2">
        <a href="{{ route('profile.edit') }}" class="px-3 py-2 bg-gray-100 rounded">Edit Profile</a>
        <a href="{{ route('profile.password.form') }}" class="px-3 py-2 bg-gray-100 rounded">Change Password</a>
    </div>
@endsection
