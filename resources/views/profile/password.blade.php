@extends('layout.app')
@section('title', 'Change Password')
@section('content')
<div class="max-w-md mx-auto bg-white rounded-xl shadow p-6">
    <h1 class="text-2xl font-bold mb-4">Change Password</h1>
    <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium">Current Password</label>
            <input type="password" name="current_password" class="mt-1 w-full border rounded px-3 py-2" required>
            @error('current_password') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">New Password</label>
            <input type="password" name="password" class="mt-1 w-full border rounded px-3 py-2" required>
            @error('password') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="mt-1 w-full border rounded px-3 py-2" required>
        </div>
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Update Password</button>
    </form>
</div>
@endsection
