<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MailAccount extends Model
{
    protected $connection = 'mailserver';
    protected $table = 'virtual_users';
    protected $primaryKey = 'id';
    
    public $timestamps = false;
    
    protected $fillable = [
        'domain_id',
        'email',
        'password',
    ];
    
    protected $hidden = [
        'password',
    ];
    
    /**
     * Get the domain for this email account
     */
    public function domain()
    {
        return DB::connection('mailserver')
            ->table('virtual_domains')
            ->where('id', $this->domain_id)
            ->first();
    }
    
    /**
     * Get domain name
     */
    public function getDomainNameAttribute()
    {
        $domain = $this->domain();
        return $domain ? $domain->name : null;
    }
    
    /**
     * Get email local part (before @)
     */
    public function getLocalPartAttribute()
    {
        return explode('@', $this->email)[0] ?? null;
    }
    
    /**
     * Hash password using Dovecot's SHA512-CRYPT format
     */
    public static function hashPassword($password)
    {
        $password = (string) $password;

        // Primary: native PHP SHA512-CRYPT, fully compatible with Dovecot.
        $saltChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./';
        $salt = '';
        for ($i = 0; $i < 16; $i++) {
            $salt .= $saltChars[random_int(0, strlen($saltChars) - 1)];
        }

        $hash = crypt($password, '$6$' . $salt . '$');
        if (is_string($hash) && str_starts_with($hash, '$6$')) {
            return '{SHA512-CRYPT}' . $hash;
        }

        // Fallback: doveadm command (if available).
        $command = 'doveadm pw -s SHA512-CRYPT -p ' . escapeshellarg($password) . ' 2>/dev/null';
        $hashed = trim((string) shell_exec($command));

        return $hashed !== '' ? $hashed : null;
    }
}
