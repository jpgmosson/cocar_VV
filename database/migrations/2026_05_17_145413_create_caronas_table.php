<?php

use App\Enums\StatusCarona;
use App\Models\PedidoCarona;
use App\Models\Trajeto;
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
        Schema::create('caronas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(PedidoCarona::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Trajeto::class)->constrained()->cascadeOnDelete();
            $table->integer('ordem_parada')->nullable();
            $table->string('status')->default(StatusCarona::ACEITA);
            $table->timestampTz('horario_embarque')->nullable();
            $table->timestampTz('horario_desembarque')->nullable();
            $table->timestamps();
            $table->unique(['pedido_carona_id', 'trajeto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caronas');
    }
};
