<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboard', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained()->onDelete('cascade');
            $table->string('campus', 100)->index();
            $table->integer('points')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard');
    }
};
