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
        Schema::create('student_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->string('previous_grade');
            $table->foreignId('previous_academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('previous_school');
            $table->string('transfer_reason');
            $table->timestamp('transfer_date');
            $table->json('previous_grades')->nullable();
            $table->string('previous_semester');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_transfers');
    }
};
