<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('user_challenges');
    }

    public function down(): void
    {
        Schema::create('user_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('challenge_type');
            $table->integer('progress')->default(0);
            $table->integer('target');
            $table->boolean('completed')->default(false);
            $table->date('expires_at');
            $table->timestamps();
        });
    }
};
