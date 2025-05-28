<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, ensure we have the basic roles
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator'],
            ['name' => 'teacher', 'display_name' => 'Teacher'],
            ['name' => 'student', 'display_name' => 'Student'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['display_name' => $role['display_name']]
            );
        }

        // Get the default role ID (student)
        $defaultRoleId = Role::where('name', 'student')->first()->id;

        // Update all users that don't have a role
        DB::table('users')
            ->whereNull('role_id')
            ->update(['role_id' => $defaultRoleId]);

        // Make role_id column not nullable
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->change();
        });
    }
};
