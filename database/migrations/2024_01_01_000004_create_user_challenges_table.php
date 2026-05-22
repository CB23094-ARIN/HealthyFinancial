<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('challenge_type'); // e.g., 'no_grab_3days', 'save_rm50'
            $table->integer('progress')->default(0);
            $table->integer('target');
            $table->boolean('completed')->default(false);
            $table->date('expires_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_challenges');
    }
};