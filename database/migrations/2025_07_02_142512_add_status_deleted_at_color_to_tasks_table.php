<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0); // 0 - актив, 1 - архив, 2 - корзина
            $table->timestamp('deleted_at')->nullable();
            $table->string('color', 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['status', 'deleted_at', 'color']);
        });
    }
};
