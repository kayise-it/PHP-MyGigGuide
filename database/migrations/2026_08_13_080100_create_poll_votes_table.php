<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->string('device_id');        // anonymous device fingerprint from app
            $table->unsignedTinyInteger('option_index');
            $table->timestamps();
            $table->unique(['poll_id', 'device_id']); // one vote per device per poll
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
    }
};
