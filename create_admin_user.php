<?php
/**
 * Script to create admin user account
 * Run: php create_admin_user.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$email = 'admin@mygigguide.co.za';
$name = 'Admin';
$username = 'admin';
$password = 'Admin@2024!';

// Check if user exists
$existing = User::where('email', $email)->first();

if ($existing) {
    echo "User already exists:\n";
    echo "  Name: {$existing->name}\n";
    echo "  Email: {$existing->email}\n";
    echo "  Username: {$existing->username}\n";
    echo "  ID: {$existing->id}\n";
} else {
    // Create user
    $user = User::create([
        'name' => $name,
        'username' => $username,
        'email' => $email,
        'password' => Hash::make($password),
        'email_verified_at' => now(),
    ]);
    
    // Assign admin role if it exists
    try {
        $adminRole = \Laratrust\Models\Role::where('name', 'admin')->first();
        if ($adminRole) {
            $user->addRole('admin');
            echo "Admin role assigned.\n";
        } else {
            echo "Warning: Admin role not found. User created without role.\n";
        }
    } catch (\Exception $e) {
        echo "Warning: Could not assign role: " . $e->getMessage() . "\n";
    }
    
    echo "User created successfully:\n";
    echo "  Name: {$user->name}\n";
    echo "  Email: {$user->email}\n";
    echo "  Username: {$user->username}\n";
    echo "  Password: {$password}\n";
    echo "  ID: {$user->id}\n";
    echo "\n";
    echo "Please change the password after first login!\n";
}

