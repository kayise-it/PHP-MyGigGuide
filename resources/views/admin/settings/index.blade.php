@extends('layouts.admin')

@section('title', 'Settings - Admin Dashboard')
@section('page-title', 'Settings')

@section('content')
<div class="max-w-4xl" x-data="settingsPage()">
    <!-- Page Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Site Settings</h2>
        <p class="mt-1 text-sm text-gray-600">Manage global site configuration and features.</p>
    </div>

    <!-- Success/Error Toast -->
    <div 
        x-show="toast.show" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        :class="toast.type === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'"
        class="fixed bottom-4 right-4 z-50 px-4 py-3 rounded-lg border shadow-lg flex items-center gap-3 max-w-md"
    >
        <template x-if="toast.type === 'success'">
            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
        </template>
        <template x-if="toast.type === 'error'">
            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
        </template>
        <span :class="toast.type === 'success' ? 'text-green-800' : 'text-red-800'" class="text-sm font-medium flex-1" x-text="toast.message"></span>
        <button 
            @click="toast.show = false" 
            class="ml-2 text-gray-400 hover:text-gray-600 transition-colors"
            aria-label="Close notification"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Authentication Settings -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Authentication Features
            </h3>
            <p class="mt-1 text-sm text-gray-500">Configure login and registration options.</p>
        </div>

        <div class="p-6 space-y-6">
            <!-- Facebook Login Toggle -->
            <div class="flex items-center justify-between p-4 rounded-lg border-2 border-gray-300 bg-gray-50/50 hover:bg-gray-50 transition-colors">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 p-2 rounded-lg bg-blue-100">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <label for="facebook_login_enabled" class="text-sm font-semibold text-gray-900 cursor-pointer">
                            Facebook Login/Registration
                        </label>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Allow users to sign in or register using their Facebook account.
                        </p>
                        <p class="text-xs mt-2">
                            <span 
                                x-show="facebookLoginEnabled" 
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                            >
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Currently Active
                            </span>
                            <span 
                                x-show="!facebookLoginEnabled" 
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600"
                            >
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                Currently Inactive
                            </span>
                        </p>
                    </div>
                </div>
                <div class="flex-shrink-0 flex items-center gap-4 ml-4">
                    <!-- Loading Spinner -->
                    <svg x-show="loading" class="animate-spin h-5 w-5 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    
                    <!-- Toggle Button -->
                    <button 
                        type="button"
                        @click="toggleFacebookLogin()"
                        :disabled="loading"
                        class="relative inline-flex h-7 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-4 focus:ring-purple-300 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="facebookLoginEnabled ? 'border-purple-600 bg-purple-600' : 'border-gray-300 bg-gray-200'"
                        role="switch"
                        :aria-checked="facebookLoginEnabled"
                    >
                        <span class="sr-only">Toggle Facebook Login</span>
                        <span
                            class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out transform"
                            :style="{ transform: facebookLoginEnabled ? 'translateX(1.75rem)' : 'translateX(0.125rem)' }"
                        ></span>
                    </button>
                </div>
            </div>

            <!-- Info Box -->
            <div class="flex items-start gap-3 p-4 rounded-lg bg-blue-50 border border-blue-200">
                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div class="text-sm text-blue-700">
                    <p class="font-medium">Note about Facebook Login</p>
                    <p class="mt-1 text-blue-600">
                        When disabled, the Facebook login button will be hidden from the login, registration, and authentication modal pages. 
                        Users who previously registered via Facebook can still log in using their email if they have set a password.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Monetization Settings -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden mt-8">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Monetization Features
            </h3>
            <p class="mt-1 text-sm text-gray-500">Configure paid features and monetization options.</p>
        </div>

        <div class="p-6 space-y-6">
            <!-- Paid Features Toggle -->
            <div class="flex items-center justify-between p-4 rounded-lg border-2 border-gray-300 bg-gray-50/50 hover:bg-gray-50 transition-colors">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 p-2 rounded-lg bg-emerald-100">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <label for="paid_features_enabled" class="text-sm font-semibold text-gray-900 cursor-pointer">
                            Paid Features
                        </label>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Enable the paid features system for premium upgrades and monetization.
                        </p>
                        <p class="text-xs mt-2">
                            <span 
                                x-show="paidFeaturesEnabled" 
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                            >
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Currently Active
                            </span>
                            <span 
                                x-show="!paidFeaturesEnabled" 
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600"
                            >
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                Currently Inactive
                            </span>
                        </p>
                    </div>
                </div>
                <div class="flex-shrink-0 flex items-center gap-4 ml-4">
                    <!-- Loading Spinner -->
                    <svg x-show="paidFeaturesLoading" class="animate-spin h-5 w-5 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    
                    <!-- Toggle Button -->
                    <button 
                        type="button"
                        @click="togglePaidFeatures()"
                        :disabled="paidFeaturesLoading"
                        class="relative inline-flex h-7 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-4 focus:ring-purple-300 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="paidFeaturesEnabled ? 'border-purple-600 bg-purple-600' : 'border-gray-300 bg-gray-200'"
                        role="switch"
                        :aria-checked="paidFeaturesEnabled"
                    >
                        <span class="sr-only">Toggle Paid Features</span>
                        <span
                            class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out transform"
                            :style="{ transform: paidFeaturesEnabled ? 'translateX(1.75rem)' : 'translateX(0.125rem)' }"
                        ></span>
                    </button>
                </div>
            </div>

            <!-- Boost Profile Toggle -->
            <div class="flex items-center justify-between p-4 rounded-lg border-2 border-gray-300 bg-gray-50/50 hover:bg-gray-50 transition-colors">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 p-2 rounded-lg bg-purple-100">
                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <label for="boost_profile_enabled" class="text-sm font-semibold text-gray-900 cursor-pointer">
                            Boost Profile Feature
                        </label>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Show or hide the "Boost Your Profile" section in user dashboards (artist, organiser, venue owner).
                        </p>
                        <p class="text-xs mt-2">
                            <span 
                                x-show="boostProfileEnabled" 
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                            >
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Currently Active
                            </span>
                            <span 
                                x-show="!boostProfileEnabled" 
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600"
                            >
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                Currently Inactive
                            </span>
                        </p>
                    </div>
                </div>
                <div class="flex-shrink-0 flex items-center gap-4 ml-4">
                    <!-- Loading Spinner -->
                    <svg x-show="boostProfileLoading" class="animate-spin h-5 w-5 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    
                    <!-- Toggle Button -->
                    <button 
                        type="button"
                        @click="toggleBoostProfile()"
                        :disabled="boostProfileLoading"
                        class="relative inline-flex h-7 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-4 focus:ring-purple-300 disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="boostProfileEnabled ? 'border-purple-600 bg-purple-600' : 'border-gray-300 bg-gray-200'"
                        role="switch"
                        :aria-checked="boostProfileEnabled"
                    >
                        <span class="sr-only">Toggle Boost Profile</span>
                        <span
                            class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out transform"
                            :style="{ transform: boostProfileEnabled ? 'translateX(1.75rem)' : 'translateX(0.125rem)' }"
                        ></span>
                    </button>
                </div>
            </div>

            <!-- Info Box -->
            <div class="flex items-start gap-3 p-4 rounded-lg bg-emerald-50 border border-emerald-200">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div class="text-sm text-emerald-700">
                    <p class="font-medium">Note about Paid Features</p>
                    <p class="mt-1 text-emerald-600">
                        When enabled, users will be able to purchase premium features such as featured listings, priority placement, and other upgrades.
                        The Paid Features management section in the admin panel will remain accessible regardless of this setting.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function settingsPage() {
    return {
        facebookLoginEnabled: {{ $settings['facebook_login_enabled'] ? 'true' : 'false' }},
        paidFeaturesEnabled: {{ $settings['paid_features_enabled'] ? 'true' : 'false' }},
        boostProfileEnabled: {{ $settings['boost_profile_enabled'] ? 'true' : 'false' }},
        loading: false,
        paidFeaturesLoading: false,
        boostProfileLoading: false,
        toast: {
            show: false,
            message: '',
            type: 'success'
        },
        toastTimeout: null,
        
        async toggleFacebookLogin() {
            this.loading = true;
            const newValue = !this.facebookLoginEnabled;
            
            try {
                const response = await fetch('{{ route('admin.settings.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        key: 'facebook_login_enabled',
                        value: newValue
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.facebookLoginEnabled = newValue;
                    this.showToast(data.message || (newValue ? 'Facebook login enabled' : 'Facebook login disabled'), 'success');
                } else {
                    this.showToast(data.message || 'Failed to update setting', 'error');
                }
            } catch (error) {
                console.error('Error toggling setting:', error);
                this.showToast('An error occurred. Please try again.', 'error');
            } finally {
                this.loading = false;
            }
        },

        async togglePaidFeatures() {
            this.paidFeaturesLoading = true;
            const newValue = !this.paidFeaturesEnabled;
            
            try {
                const response = await fetch('{{ route('admin.settings.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        key: 'paid_features_enabled',
                        value: newValue
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.paidFeaturesEnabled = newValue;
                    this.showToast(data.message || (newValue ? 'Paid features enabled' : 'Paid features disabled'), 'success');
                } else {
                    this.showToast(data.message || 'Failed to update setting', 'error');
                }
            } catch (error) {
                console.error('Error toggling setting:', error);
                this.showToast('An error occurred. Please try again.', 'error');
            } finally {
                this.paidFeaturesLoading = false;
            }
        },

        async toggleBoostProfile() {
            this.boostProfileLoading = true;
            const newValue = !this.boostProfileEnabled;
            
            try {
                const response = await fetch('{{ route('admin.settings.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        key: 'boost_profile_enabled',
                        value: newValue
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.boostProfileEnabled = newValue;
                    this.showToast(data.message || (newValue ? 'Boost Profile enabled' : 'Boost Profile disabled'), 'success');
                } else {
                    this.showToast(data.message || 'Failed to update setting', 'error');
                }
            } catch (error) {
                console.error('Error toggling setting:', error);
                this.showToast('An error occurred. Please try again.', 'error');
            } finally {
                this.boostProfileLoading = false;
            }
        },
        
        showToast(message, type = 'success') {
            // Clear any existing timeout
            if (this.toastTimeout) {
                clearTimeout(this.toastTimeout);
            }
            
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            
            // Auto-dismiss after 5 seconds (increased from 3)
            this.toastTimeout = setTimeout(() => {
                this.toast.show = false;
            }, 5000);
        }
    }
}
</script>
@endpush
@endsection
