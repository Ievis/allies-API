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
        Schema::create('telegram_dating_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('first_user_id')->constrained('telegram_dating_users')->cascadeOnDelete();
            $table->foreignId('second_user_id')->constrained('telegram_dating_users')->cascadeOnDelete();
            $table->boolean('first_user_reaction')->default(false);
            $table->boolean('second_user_reaction')->default(false);
            $table->string('subject');
            $table->string('category');
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_dating_feedback');
    }
};
