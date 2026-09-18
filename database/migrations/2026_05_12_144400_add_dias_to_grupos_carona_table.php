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
        Schema::table('grupos_carona', function (Blueprint $table) {
            $table->json('dias_semana')->nullable()->after('frequencia');
            $table->json('dias_mes')->nullable()->after('dias_semana');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupos_carona', function (Blueprint $table) {
            $table->dropColumn(['dias_semana', 'dias_mes']);
        });
    }
};
