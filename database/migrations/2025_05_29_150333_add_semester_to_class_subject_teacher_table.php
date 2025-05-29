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
        Schema::table('class_subject_teacher', function (Blueprint $table) {
            $table->unsignedTinyInteger('semester')->after('academic_year_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_subject_teacher', function (Blueprint $table) {
            $table->dropColumn('semester');
        });
    }
};
