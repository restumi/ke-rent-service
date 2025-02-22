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
        Schema::create('users_status', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['admin','user']);
            $table->boolean('is_banned')->default(false);
            $table->dateTime('last_seen')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_status');
    }
};
