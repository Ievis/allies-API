<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('telegram_dating_users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('name');
            $table->text('about');
            $table->string('subject');
            $table->string('category');
            $table->boolean('is_waiting')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_dating_users');
    }
};
