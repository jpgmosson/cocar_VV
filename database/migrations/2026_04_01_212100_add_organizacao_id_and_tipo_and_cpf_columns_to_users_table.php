<?php

use App\Enums\TipoUsuario;
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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organizacao_id')->nullable()->constrained('organizacoes')->cascadeOnDelete();
            $table->string('tipo')->default(TipoUsuario::PADRAO);
            $table->string('cpf', 11)->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organizacao_id');
            $table->dropColumn('tipo');
            $table->dropColumn('cpf');
        });
    }
};
