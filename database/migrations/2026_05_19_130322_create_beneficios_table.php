<?php

use App\Models\Organizacao;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficios', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Organizacao::class)->constrained()->cascadeOnDelete();

            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->decimal('meta_km', 8, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beneficios');
    }
};
