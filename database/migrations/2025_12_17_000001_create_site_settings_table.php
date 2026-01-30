<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json, etc.
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default setting for Facebook login (disabled by default)
        \DB::table('site_settings')->insert([
            'key' => 'facebook_login_enabled',
            'value' => '0',
            'type' => 'boolean',
            'description' => 'Enable or disable Facebook login/registration feature',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};


