<?php

use App\Enums\StatusTrajeto;
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
        Schema::create('trajetos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->geography('localizacao_motorista', subtype: 'point')->nullable();
            $table->geography('origem_coords', subtype: 'point')->index();
            $table->string('origem_endereco');
            $table->geography('destino_coords', subtype: 'point')->index();
            $table->string('destino_endereco');
            $table->geography('rota', subtype: 'lineString')->index();
            $table->double('distancia_percorrida')->default(0);
            $table->decimal('custo')->default(0);
            $table->string('status')->default(StatusTrajeto::PLANEJADO);
            $table->timestampTz('horario_inicio')->nullable();
            $table->timestampTz('horario_fim')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trajetos');
    }
};
