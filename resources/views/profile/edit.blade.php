@extends('layout.app')
@section('title', 'Edit Profile')
@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-6">
    <h1 class="text-2xl font-bold mb-4">Edit Profile</h1>
    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-medium">First Name</label>
                <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" class="mt-1 w-full border rounded px-3 py-2">
                @error('first_name') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Middle Name</label>
                <input type="text" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}" class="mt-1 w-full border rounded px-3 py-2">
                @error('middle_name') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Last Name</label>
                <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" class="mt-1 w-full border rounded px-3 py-2">
                @error('last_name') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 w-full border rounded px-3 py-2">
            @error('email') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Bio</label>
            <textarea name="bio" class="mt-1 w-full border rounded px-3 py-2" rows="4">{{ old('bio', optional($user->profile)->bio ?? $user->bio) }}</textarea>
            @error('bio') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', optional($user->profile)->phone) }}" class="mt-1 w-full border rounded px-3 py-2">
            @error('phone') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Address</label>
            <input type="text" name="address" value="{{ old('address', optional($user->profile)->address) }}" class="mt-1 w-full border rounded px-3 py-2">
            @error('address') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        <div class="flex gap-2">
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Save</button>
            <a href="{{ route('profile.show') }}" class="px-3 py-2 bg-gray-100 rounded">Cancel</a>
        </div>
    </form>
</div>
@endsection
