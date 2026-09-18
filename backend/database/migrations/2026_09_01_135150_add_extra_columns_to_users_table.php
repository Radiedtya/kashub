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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->after('password')->constrained('roles')->onDelete('restrict');
            $table->string('no_hp', 15)->after('role_id')->nullable();
            $table->string('foto')->after('no_hp')->nullable();
            $table->boolean('is_active')->after('foto')->default(true);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'no_hp', 'foto', 'is_active']);
        });
    }
};
