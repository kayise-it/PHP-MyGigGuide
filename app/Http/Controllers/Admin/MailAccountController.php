<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailAccount;
use App\Services\MailCredentialsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MailAccountController extends Controller
{
    /**
     * Resolve the single/default mail domain.
     */
    private function getDefaultDomain()
    {
        return DB::connection('mailserver')
            ->table('virtual_domains')
            ->select('id', 'name')
            ->orderBy('id')
            ->first();
    }

    /**
     * Normalize a username/full email into a validated full email.
     */
    private function normalizeEmail(string $inputEmail, int $domainId): array
    {
        $emailInput = trim($inputEmail);
        if ($emailInput === '') {
            return [null, 'Email address is required.'];
        }

        if (!str_contains($emailInput, '@')) {
            if (!preg_match('/^[A-Za-z0-9._%+\-]+$/', $emailInput)) {
                return [null, 'Use only letters, numbers, dot, underscore, plus or dash in the username.'];
            }

            $domain = DB::connection('mailserver')
                ->table('virtual_domains')
                ->where('id', $domainId)
                ->value('name');

            if (!$domain) {
                return [null, 'No valid domain is configured.'];
            }

            return [$emailInput.'@'.$domain, null];
        }

        if (!filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
            return [null, 'Please enter a valid email address.'];
        }

        return [strtolower($emailInput), null];
    }

    /**
     * Display a listing of email accounts
     */
    public function index(Request $request)
    {
        $query = DB::connection('mailserver')
            ->table('virtual_users')
            ->join('virtual_domains', 'virtual_users.domain_id', '=', 'virtual_domains.id')
            ->select('virtual_users.*', 'virtual_domains.name as domain_name');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('virtual_users.email', 'like', "%{$search}%");
        }

        // Domain filter
        if ($request->filled('domain')) {
            $query->where('virtual_domains.name', $request->domain);
        }

        $accounts = $query->orderBy('virtual_users.email')->paginate(20);
        
        // Get all domains for filter
        $domains = DB::connection('mailserver')
            ->table('virtual_domains')
            ->distinct()
            ->pluck('name');

        return view('admin.mail-accounts.index', compact('accounts', 'domains'));
    }

    /**
     * Show the form for creating a new email account
     */
    public function create()
    {
        $defaultDomain = $this->getDefaultDomain();

        if (!$defaultDomain) {
            return redirect()->route('admin.mail-accounts.index')
                ->with('error', 'No mail domain is configured yet.');
        }

        return view('admin.mail-accounts.create', compact('defaultDomain'));
    }

    /**
     * Store a newly created email account
     */
    public function store(Request $request)
    {
        $domainId = $request->input('domain_id');
        if (empty($domainId)) {
            $domainId = $this->getDefaultDomain()?->id;
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:120',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'Email address is required.',
            'email.max' => 'Email address cannot exceed 120 characters.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (empty($domainId) || !DB::connection('mailserver')->table('virtual_domains')->where('id', $domainId)->exists()) {
            return redirect()->back()
                ->withErrors(['domain_id' => 'No valid domain is configured.'])
                ->withInput();
        }

        [$email, $emailError] = $this->normalizeEmail((string) $request->email, (int) $domainId);
        if ($emailError) {
            return redirect()->back()
                ->withErrors(['email' => $emailError])
                ->withInput();
        }

        // Check if email already exists
        $exists = DB::connection('mailserver')
            ->table('virtual_users')
            ->where('email', $email)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors(['email' => 'This email address already exists.'])
                ->withInput();
        }

        // Hash password in Dovecot-compatible format
        $hashedPassword = MailAccount::hashPassword($request->password);
        if (empty($hashedPassword)) {
            return redirect()->back()
                ->withErrors(['password' => 'Failed to hash password. Please try again.'])
                ->withInput();
        }

        // Create email account
        DB::connection('mailserver')->table('virtual_users')->insert([
            'domain_id' => $domainId,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        return redirect()->route('admin.mail-accounts.index')
            ->with('success', 'Email account created successfully.');
    }

    /**
     * Display the specified email account with configuration
     */
    public function show($id)
    {
        $account = DB::connection('mailserver')
            ->table('virtual_users')
            ->join('virtual_domains', 'virtual_users.domain_id', '=', 'virtual_domains.id')
            ->select('virtual_users.*', 'virtual_domains.name as domain_name')
            ->where('virtual_users.id', $id)
            ->first();

        if (!$account) {
            abort(404, 'Email account not found.');
        }

        // Email client configuration
        $config = [
            'incoming' => [
                'server' => 'mail.mygigguide.co.za',
                'port' => 143,
                'security' => 'STARTTLS',
                'username' => $account->email,
                'password' => '*** (hidden)',
            ],
            'outgoing' => [
                'server' => 'mail.mygigguide.co.za',
                'port' => 587,
                'security' => 'STARTTLS',
                'username' => $account->email,
                'password' => '*** (hidden)',
            ],
        ];

        return view('admin.mail-accounts.show', compact('account', 'config'));
    }

    /**
     * Show the form for editing the specified email account
     */
    public function edit($id)
    {
        $account = DB::connection('mailserver')
            ->table('virtual_users')
            ->where('id', $id)
            ->first();

        if (!$account) {
            abort(404, 'Email account not found.');
        }

        $domainName = DB::connection('mailserver')
            ->table('virtual_domains')
            ->where('id', $account->domain_id)
            ->value('name');

        return view('admin.mail-accounts.edit', compact('account', 'domainName'));
    }

    /**
     * Update the specified email account
     */
    public function update(Request $request, $id)
    {
        $account = DB::connection('mailserver')
            ->table('virtual_users')
            ->where('id', $id)
            ->first();

        if (!$account) {
            abort(404, 'Email account not found.');
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:120',
            'password' => 'nullable|string|min:8|confirmed',
            'domain_id' => 'required',
        ], [
            'email.required' => 'Email address is required.',
            'email.max' => 'Email address cannot exceed 120 characters.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'domain_id.required' => 'Domain is required.',
        ]);

        if (!DB::connection('mailserver')->table('virtual_domains')->where('id', $request->domain_id)->exists()) {
            return redirect()->back()
                ->withErrors(['domain_id' => 'Selected domain does not exist.'])
                ->withInput();
        }

        [$normalizedEmail, $emailError] = $this->normalizeEmail((string) $request->email, (int) $request->domain_id);
        if ($emailError) {
            return redirect()->back()
                ->withErrors(['email' => $emailError])
                ->withInput();
        }

        // Check if email already exists (excluding current account)
        if ($normalizedEmail !== $account->email) {
            $exists = DB::connection('mailserver')
                ->table('virtual_users')
                ->where('email', $normalizedEmail)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                $validator->errors()->add('email', 'This email address already exists.');
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $updateData = [
            'email' => $normalizedEmail,
            'domain_id' => $request->domain_id,
        ];

        // Update password only if provided
        if ($request->filled('password')) {
            $hashedPassword = MailAccount::hashPassword($request->password);
            if (empty($hashedPassword)) {
                return redirect()->back()
                    ->withErrors(['password' => 'Failed to hash password. Please try again.'])
                    ->withInput();
            }
            $updateData['password'] = $hashedPassword;
        }

        DB::connection('mailserver')
            ->table('virtual_users')
            ->where('id', $id)
            ->update($updateData);

        // Store SMTP credentials (encrypted) when the default app mail account is updated.
        // Laravel reads these at runtime instead of .env, so the password is never in .env.
        $defaultAccountId = (int) config('mail.default_account_id', 2);
        if ((int) $id === $defaultAccountId) {
            $plainPassword = $request->filled('password') ? $request->password : null;
            MailCredentialsService::store($normalizedEmail, $plainPassword);
        }

        return redirect()->route('admin.mail-accounts.show', $id)
            ->with('success', 'Email account updated successfully.');
    }

    /**
     * Remove the specified email account
     */
    public function destroy($id)
    {
        $account = DB::connection('mailserver')
            ->table('virtual_users')
            ->where('id', $id)
            ->first();

        if (!$account) {
            abort(404, 'Email account not found.');
        }

        DB::connection('mailserver')
            ->table('virtual_users')
            ->where('id', $id)
            ->delete();

        return redirect()->route('admin.mail-accounts.index')
            ->with('success', 'Email account deleted successfully.');
    }
}
