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
        // No need to add these columns since they are in users table
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No columns to drop
    }
};
