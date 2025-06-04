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
        Schema::table('cuidadores', function (Blueprint $table) {
            $table->integer('tarifa_hora')->nullable()->comment('Tarifa por hora en pesos chilenos')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuidadores', function (Blueprint $table) {
            $table->decimal('tarifa_hora', 8, 2)->nullable()->change();
        });
    }
};
