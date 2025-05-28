<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->string('promotion_status')->default('pending')->after('teacher_id');
        });
    }

    public function down()
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropColumn('promotion_status');
        });
    }
};
