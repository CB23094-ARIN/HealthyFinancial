<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'campus')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('campus', 'university_name');
            });
        }

        if (Schema::hasColumn('leaderboard', 'campus')) {
            Schema::table('leaderboard', function (Blueprint $table) {
                $table->renameColumn('campus', 'university_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'university_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('university_name', 'campus');
            });
        }

        if (Schema::hasColumn('leaderboard', 'university_name')) {
            Schema::table('leaderboard', function (Blueprint $table) {
                $table->renameColumn('university_name', 'campus');
            });
        }
    }
};
