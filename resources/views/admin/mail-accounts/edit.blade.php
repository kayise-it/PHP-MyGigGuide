@extends('layouts.admin')

@section('title', 'Edit Email Account - My Gig Guide')
@section('page-title', 'Edit Email Account')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Email Account</h2>
            <p class="mt-1 text-sm text-gray-600">Update email account settings</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.mail-accounts.show', $account->id) }}" class="btn-secondary">
                View Config
            </a>
            <a href="{{ route('admin.mail-accounts.index') }}" class="btn-secondary">
                Back to List
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
        <form action="{{ route('admin.mail-accounts.update', $account->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Domain Selection -->
            <div class="mb-6">
                <label for="domain_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Domain <span class="text-red-500">*</span>
                </label>
                <select name="domain_id" id="domain_id" required
                        class="form-input @error('domain_id') border-red-500 @enderror">
                    @foreach($domains as $id => $name)
                        <option value="{{ $id }}" {{ old('domain_id', $account->domain_id) == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                @error('domain_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="email"
                    value="{{ old('email', $account->email) }}"
                    required
                    class="form-input @error('email') border-red-500 @enderror"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password (Optional) -->
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    New Password <span class="text-gray-500 text-xs">(leave blank to keep current)</span>
                </label>
                <input 
                    type="password" 
                    name="password" 
                    id="password"
                    minlength="8"
                    class="form-input @error('password') border-red-500 @enderror"
                >
                <p class="mt-1 text-sm text-gray-500">Minimum 8 characters. Leave blank to keep current password.</p>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    Confirm New Password
                </label>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    id="password_confirmation"
                    minlength="8"
                    class="form-input"
                >
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.mail-accounts.show', $account->id) }}" class="btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    Update Email Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
