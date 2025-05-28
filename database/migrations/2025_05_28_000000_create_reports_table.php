<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('semester');
            $table->text('homeroom_teacher_notes')->nullable();
            $table->text('principal_notes')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
