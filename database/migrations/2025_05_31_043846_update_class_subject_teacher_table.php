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
        // Drop unused tables if they exist
        if (Schema::hasTable('class_subject')) {
            Schema::dropIfExists('class_subject');
        }

        if (Schema::hasTable('teacher_subject')) {
            Schema::dropIfExists('teacher_subject');
        }

        // Create new class_subject_teacher table if it doesn't exist
        if (!Schema::hasTable('class_subject_teacher')) {
            Schema::create('class_subject_teacher', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_class_id')->constrained()->onDelete('cascade');
                $table->foreignId('subject_id')->constrained()->onDelete('cascade');
                $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
                $table->enum('semester', ['odd', 'even'])->default('odd');
                $table->timestamps();

                $table->unique(['school_class_id', 'subject_id', 'teacher_id', 'academic_year_id', 'semester'], 'unique_class_subject_teacher');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to recreate the old tables in down() since they're no longer used
        Schema::dropIfExists('class_subject_teacher');
    }
};
