<?php

use App\Enums\StatusPedidoCarona;
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
        Schema::create('pedidos_carona', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->geography('origem_coords', subtype: 'point')->index();
            $table->geography('destino_coords', subtype: 'point')->index();
            $table->string('origem_endereco');
            $table->string('destino_endereco');
            $table->string('status')->default(StatusPedidoCarona::PROCURANDO_MOTORISTA);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos_carona');
    }
};
