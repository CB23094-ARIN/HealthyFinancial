<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category');
            $table->decimal('monthly_limit', 10, 2);
            $table->date('month_year');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('budgets');
    }
};