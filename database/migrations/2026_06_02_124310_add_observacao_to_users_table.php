<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {

            $table->string('observacao', 20)->nullable();

            // CASO INT (Ex: Idade, Número de Caronas):
            // $table->integer('nome_do_campo')->default(0);

            // CASO NUMERIC/DOUBLE (Ex: Limite de Crédito, Peso):
            // $table->double('nome_do_campo', 8, 2)->default(0.00);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('observacao');
        });
    }
};
