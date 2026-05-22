<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('monthly_allowance', 10, 2)->default(0);
            $table->decimal('ptptn_balance', 10, 2)->default(0);
            $table->integer('saving_streak')->default(0);
            $table->string('campus')->nullable();
            $table->boolean('ptptn_mode')->default(false);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['monthly_allowance', 'ptptn_balance', 'saving_streak', 'campus', 'ptptn_mode']);
        });
    }
};