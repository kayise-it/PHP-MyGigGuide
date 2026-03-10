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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('date');
            $table->time('time');
            $table->decimal('price', 8, 2)->nullable();
            $table->string('ticket_url')->nullable();
            $table->string('poster')->nullable();
            $table->text('gallery')->nullable(); // JSON string
            $table->string('status')->default('upcoming');
            $table->string('category')->nullable();
            $table->integer('capacity')->nullable();
            $table->foreignId('venue_id')->nullable()->constrained();
            $table->morphs('owner'); // owner_id, owner_type
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
