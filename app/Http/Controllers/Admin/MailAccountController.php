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
        $domains = DB::connection('mailserver')
            ->table('virtual_domains')
            ->distinct()
            ->pluck('name', 'id');

        return view('admin.mail-accounts.create', compact('domains'));
    }

    /**
     * Store a newly created email account
     */
    public function store(Request $request)
    {
        // #region agent log
        $logPath = base_path('.cursor/debug.log');
        $log = function ($hypothesisId, $message, $data = []) use ($logPath) {
            $line = json_encode(array_filter([
                'timestamp' => (int)(microtime(true) * 1000),
                'sessionId' => 'debug-session',
                'runId' => $data['runId'] ?? 'run1',
                'hypothesisId' => $hypothesisId,
                'location' => 'MailAccountController.php:store',
                'message' => $message,
                'data' => $data,
            ])) . "\n";
            @file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX);
        };
        $log('A', 'store() entry', ['email_raw' => $request->input('email'), 'domain_id' => $request->input('domain_id'), 'has_at' => str_contains((string)$request->input('email'), '@')]);
        // #endregion

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:120',
            'password' => 'required|string|min:8|confirmed',
            'domain_id' => 'required|exists:mailserver.virtual_domains,id',
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email address cannot exceed 120 characters.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'domain_id.required' => 'Domain is required.',
            'domain_id.exists' => 'Selected domain does not exist.',
        ]);

        // #region agent log
        $log('A', 'after Validator::make', ['fails' => $validator->fails(), 'errors' => $validator->errors()->get('email')]);
        // #endregion

        // Check if email already exists
        $exists = DB::connection('mailserver')
            ->table('virtual_users')
            ->where('email', $request->email)
            ->exists();

        // #region agent log
        $log('C', 'duplicate check', ['request_email' => $request->email, 'exists' => $exists]);
        // #endregion

        if ($exists) {
            $validator->errors()->add('email', 'This email address already exists.');
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($validator->fails()) {
            // #region agent log
            $log('E', 'redirect back due to validation failure', ['failed_rules' => $validator->errors()->keys()]);
            // #endregion
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Hash password using Dovecot
        $hashedPassword = MailAccount::hashPassword($request->password);

        // Construct full email if only username provided
        $email = $request->email;
        // #region agent log
        $log('E', 'before construct full email', ['email' => $email, 'will_append_domain' => !str_contains($email, '@')]);
        // #endregion
        if (!str_contains($email, '@')) {
            $domain = DB::connection('mailserver')
                ->table('virtual_domains')
                ->where('id', $request->domain_id)
                ->value('name');
            $email = $email . '@' . $domain;
        }

        // Create email account
        DB::connection('mailserver')->table('virtual_users')->insert([
            'domain_id' => $request->domain_id,
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
                'security' => 'None',
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

        $domains = DB::connection('mailserver')
            ->table('virtual_domains')
            ->distinct()
            ->pluck('name', 'id');

        return view('admin.mail-accounts.edit', compact('account', 'domains'));
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
            'email' => 'required|email|max:120',
            'password' => 'nullable|string|min:8|confirmed',
            'domain_id' => 'required|exists:mailserver.virtual_domains,id',
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email address cannot exceed 120 characters.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'domain_id.required' => 'Domain is required.',
            'domain_id.exists' => 'Selected domain does not exist.',
        ]);

        // Check if email already exists (excluding current account)
        if ($request->email !== $account->email) {
            $exists = DB::connection('mailserver')
                ->table('virtual_users')
                ->where('email', $request->email)
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
            'email' => $request->email,
            'domain_id' => $request->domain_id,
        ];

        // Update password only if provided
        if ($request->filled('password')) {
            $updateData['password'] = MailAccount::hashPassword($request->password);
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
            MailCredentialsService::store($request->email, $plainPassword);
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
