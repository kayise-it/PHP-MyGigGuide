<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->string('context');          // e.g. 'vowfm', 'risefm', 'hot1027'
            $table->string('question');
            $table->json('options');            // array of option label strings
            $table->boolean('active')->default(true);
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();
            $table->index(['context', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polls');
    }
};
