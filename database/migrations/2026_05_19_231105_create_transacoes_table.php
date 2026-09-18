<?php

use App\Models\PedidoCarona;
use App\Models\Trajeto;
use App\Models\User;
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
        Schema::create('transacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PedidoCarona::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Trajeto::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('tipo');
            $table->string('status');
            $table->decimal('valor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacoes');
    }
};
