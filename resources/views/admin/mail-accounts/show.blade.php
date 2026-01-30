@extends('layouts.admin')

@section('title', 'Email Account Configuration - My Gig Guide')
@section('page-title', 'Email Account Configuration')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $account->email }}</h2>
            <p class="mt-1 text-sm text-gray-600">Email account configuration and client setup</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.mail-accounts.edit', $account->id) }}" class="btn-primary">
                Edit Account
            </a>
            <a href="{{ route('admin.mail-accounts.index') }}" class="btn-secondary">
                Back to List
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Account Information -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $account->email }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Domain</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $account->domain_name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Account ID</dt>
                <dd class="mt-1 text-sm text-gray-900">#{{ $account->id }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Webmail Access</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    <a href="https://mail.mygigguide.co.za" target="_blank" class="text-purple-600 hover:text-purple-900">
                        https://mail.mygigguide.co.za
                    </a>
                </dd>
            </div>
        </dl>
    </div>

    <!-- Email Client Configuration -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Email Client Configuration</h3>
        <p class="text-sm text-gray-600 mb-6">Use these settings to configure your email client (Outlook, Thunderbird, Apple Mail, etc.)</p>

        <!-- Incoming Mail Server (IMAP) -->
        <div class="mb-8">
            <h4 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Incoming Mail Server (IMAP)
            </h4>
            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Server</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono bg-white px-3 py-2 rounded border">{{ $config['incoming']['server'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Port</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono bg-white px-3 py-2 rounded border">{{ $config['incoming']['port'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Security</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono bg-white px-3 py-2 rounded border">{{ $config['incoming']['security'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Username</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono bg-white px-3 py-2 rounded border">{{ $config['incoming']['username'] }}</dd>
                    </div>
                </div>
                <div class="pt-2 border-t border-gray-200">
                    <p class="text-xs text-gray-500">
                        <strong>Note:</strong> Use your email account password. Authentication method: Normal password.
                    </p>
                </div>
            </div>
        </div>

        <!-- Outgoing Mail Server (SMTP) -->
        <div class="mb-8">
            <h4 class="text-md font-semibold text-gray-900 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                Outgoing Mail Server (SMTP)
            </h4>
            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Server</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono bg-white px-3 py-2 rounded border">{{ $config['outgoing']['server'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Port</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono bg-white px-3 py-2 rounded border">{{ $config['outgoing']['port'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Security</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono bg-white px-3 py-2 rounded border">{{ $config['outgoing']['security'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Username</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono bg-white px-3 py-2 rounded border">{{ $config['outgoing']['username'] }}</dd>
                    </div>
                </div>
                <div class="pt-2 border-t border-gray-200">
                    <p class="text-xs text-gray-500">
                        <strong>Note:</strong> Authentication is required. Use the same username and password as incoming mail.
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Setup Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-blue-900 mb-2">Quick Setup Instructions</h4>
            <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                <li>Open your email client (Outlook, Thunderbird, Apple Mail, etc.)</li>
                <li>Add a new email account</li>
                <li>Enter your email address: <strong>{{ $account->email }}</strong></li>
                <li>Use the settings above for IMAP and SMTP servers</li>
                <li>Make sure to enable authentication for SMTP</li>
                <li>For SMTP security, select "STARTTLS" or "TLS"</li>
            </ul>
        </div>
    </div>

    <!-- Copy Configuration Button -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Copy Configuration</h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">IMAP Settings</label>
                <div class="flex items-center gap-2">
                    <textarea readonly class="form-input font-mono text-sm" rows="4" id="imap-config">Incoming Mail Server (IMAP)
Server: {{ $config['incoming']['server'] }}
Port: {{ $config['incoming']['port'] }}
Security: {{ $config['incoming']['security'] }}
Username: {{ $config['incoming']['username'] }}
Password: [Your email password]</textarea>
                    <button onclick="copyToClipboard('imap-config')" class="btn-secondary whitespace-nowrap">
                        Copy
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Settings</label>
                <div class="flex items-center gap-2">
                    <textarea readonly class="form-input font-mono text-sm" rows="4" id="smtp-config">Outgoing Mail Server (SMTP)
Server: {{ $config['outgoing']['server'] }}
Port: {{ $config['outgoing']['port'] }}
Security: {{ $config['outgoing']['security'] }}
Username: {{ $config['outgoing']['username'] }}
Password: [Your email password]
Authentication: Required</textarea>
                    <button onclick="copyToClipboard('smtp-config')" class="btn-secondary whitespace-nowrap">
                        Copy
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    document.execCommand('copy');
    
    // Show feedback
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Copied!';
    button.classList.add('bg-green-100', 'text-green-800');
    
    setTimeout(() => {
        button.textContent = originalText;
        button.classList.remove('bg-green-100', 'text-green-800');
    }, 2000);
}
</script>
@endsection
