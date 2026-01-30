@extends('layouts.admin')

@section('title', 'Create Email Account - My Gig Guide')
@section('page-title', 'Create Email Account')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create Email Account</h2>
            <p class="mt-1 text-sm text-gray-600">Create a new email account for @mygigguide.co.za</p>
        </div>
        <a href="{{ route('admin.mail-accounts.index') }}" class="btn-secondary">
            Back to List
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
        <form action="{{ route('admin.mail-accounts.store') }}" method="POST">
            @csrf

            <!-- Domain Selection -->
            <div class="mb-6">
                <label for="domain_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Domain <span class="text-red-500">*</span>
                </label>
                <select name="domain_id" id="domain_id" required
                        class="form-input @error('domain_id') border-red-500 @enderror">
                    <option value="">Select a domain</option>
                    @foreach($domains as $id => $name)
                        <option value="{{ $id }}" {{ old('domain_id') == $id ? 'selected' : '' }}>
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
                <div class="flex items-center">
                    <input 
                        type="text" 
                        name="email" 
                        id="email"
                        value="{{ old('email') }}"
                        placeholder="username"
                        required
                        class="form-input rounded-r-none @error('email') border-red-500 @enderror"
                    >
                    <span class="px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-md text-gray-700">
                        @mygigguide.co.za
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">Enter only the username part (before @)</p>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    name="password" 
                    id="password"
                    required
                    minlength="8"
                    class="form-input @error('password') border-red-500 @enderror"
                >
                <p class="mt-1 text-sm text-gray-500">Minimum 8 characters</p>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    Confirm Password <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    id="password_confirmation"
                    required
                    minlength="8"
                    class="form-input"
                >
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.mail-accounts.index') }}" class="btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    Create Email Account
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const domainSelect = document.getElementById('domain_id');
    
    // Auto-update email when domain changes
    domainSelect.addEventListener('change', function() {
        const domain = this.options[this.selectedIndex].text;
        // You can add logic here if needed
    });
});
</script>
@endsection
