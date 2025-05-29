<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom academic_year_id nullable
        Schema::table('class_subject', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->after('school_class_id');
        });

        // 2. Isi academic_year_id dengan tahun ajaran aktif
        $activeYear = DB::table('academic_years')->where('is_active', true)->first();
        if ($activeYear) {
            DB::table('class_subject')->update(['academic_year_id' => $activeYear->id]);
        } else {
            // fallback: isi dengan tahun ajaran pertama jika tidak ada yang aktif
            $firstYear = DB::table('academic_years')->orderBy('id')->first();
            if ($firstYear) {
                DB::table('class_subject')->update(['academic_year_id' => $firstYear->id]);
            }
        }

        // 3. Set kolom jadi not nullable dan tambahkan foreign key
        Schema::table('class_subject', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->change();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_subject', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
