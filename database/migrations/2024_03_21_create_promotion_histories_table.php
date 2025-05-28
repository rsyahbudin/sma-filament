<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('promotion_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users');
            $table->foreignId('from_class_id')->constrained('school_classes');
            $table->foreignId('to_class_id')->constrained('school_classes');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->decimal('average_score', 5, 2);
            $table->integer('failed_subjects');
            $table->boolean('is_promoted');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotion_histories');
    }
};
