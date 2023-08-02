<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->integer('number_in_course');
            $table->string('url')->nullable();
            $table->string('zoom_url')->nullable();
            $table->string('will_at')->nullable();
            $table->string('title');
            $table->string('description')->nullable();
            $table->boolean('is_modification')->default(true);
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('lesson_types');
            $table->foreignId('status_id')->constrained('lesson_statuses');
            $table->foreignId('section_id')->constrained('sections');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lessons');
    }
};
