<?php

use App\Models\PerfilMotorista;
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
        Schema::create('grupos_carona', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(PerfilMotorista::class)->constrained()->cascadeOnDelete();
            $table->string('nome');
            $table->enum('frequencia', ['semanal', 'mensal']);
            $table->integer('vagas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos_carona');
    }
};
