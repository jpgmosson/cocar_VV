<?php

use App\Actions\Fortify\DeleteUser;
use App\Enums\TipoUsuario;
use App\Http\Controllers\Admin\OrganizacaoController;
use App\Http\Controllers\Admin\PainelController;
use App\Http\Controllers\Admin\TriagemMotoristaController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Auth\CadastroOrganizacaoController;
use App\Http\Controllers\CarteiraController;
use App\Http\Controllers\GrupoCaronaController;
use App\Http\Controllers\HomeMotoristaController;
use App\Http\Controllers\HomePassageiroController;
use App\Http\Controllers\Organizacao\BeneficioController;
use App\Http\Controllers\PedidoCaronaController;
use App\Http\Controllers\PerfilMotoristaController;
use App\Http\Controllers\TrajetoController;
use App\Http\Controllers\TransacaoController;
use App\Http\Middleware\VerificarTipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('index');

Route::middleware('guest')->group(function () {
    Route::get('/cadastro/organizacao', [CadastroOrganizacaoController::class, 'formulario'])->name('cadastro-organizacao');
    Route::post('/organizacoes', [CadastroOrganizacaoController::class, 'cadastrar'])->name('organizacao.criar');
});

Route::middleware(['auth', VerificarTipo::sendo(TipoUsuario::ADMINISTRADOR_ORGANIZACAO, TipoUsuario::ADMINISTRADOR_SISTEMA)])->prefix('/dashboard')->group(function () {
    Route::get('/', [PainelController::class, 'exibir'])->name('admin.painel');
    Route::get('triagem-motorista', [TriagemMotoristaController::class, 'exibir'])->name('admin.triagem-motoristas');
    Route::post('triagem-motorista/{perfilMotorista}/rejeitar', [TriagemMotoristaController::class, 'rejeitar'])->name('triagem-motoristas.rejeitar');
    Route::post('triagem-motorista/{perfilMotorista}/aprovar', [TriagemMotoristaController::class, 'aprovar'])->name('triagem-motoristas.aprovar');
    Route::get('usuarios', [UsuarioController::class, 'exibir'])->name('admin.usuarios');
    Route::get('cadastro', fn () => view('admin.configuracoes'))->name('admin.meu-cadastro');
    Route::put('cadastro/{organizacao}', [OrganizacaoController::class, 'alterar'])->name('organizacoes.alterar');

    Route::get('/beneficios', [BeneficioController::class, 'index'])->name('admin.beneficios.index');
    Route::get('/beneficios/criar', [BeneficioController::class, 'create'])->name('admin.beneficios.criar');
    Route::post('/beneficios', [BeneficioController::class, 'store'])->name('admin.beneficios.store');
    Route::delete('/beneficios/{beneficio}', [BeneficioController::class, 'destroy'])->name('admin.beneficios.remover');
    Route::get('/beneficios/{beneficio}', [BeneficioController::class, 'edit'])->name('admin.beneficios.editar');
    Route::put('/beneficios/{beneficio}', [BeneficioController::class, 'update'])->name('admin.beneficios.atualizar');

    Route::get('organizacoes', [OrganizacaoController::class, 'listar'])->name('admin.organizacoes');
    Route::delete('cadastro', [CadastroOrganizacaoController::class, 'deletar'])->name('admin.meu-cadastro.deletar');
});

Route::middleware(['auth', VerificarTipo::sendo(TipoUsuario::PADRAO)])->group(function () {
    Route::post('/motorista', [PerfilMotoristaController::class, 'criar'])->name('motorista.cadastro');
    Route::get('/home/motorista', [HomeMotoristaController::class, 'mostrar'])->name('motorista.home');
    Route::get('/grupos', [GrupoCaronaController::class, 'index'])->name('grupos.index');
    Route::get('/motorista/criar', [GrupoCaronaController::class, 'create'])->name('motorista.grupos.criar');
    Route::post('/motorista/grupos', [GrupoCaronaController::class, 'store'])->name('motorista.grupos.store');

    Route::delete('/motorista/grupos/{grupo}', [GrupoCaronaController::class, 'destroy'])->name('motorista.grupos.destroy');

    Route::post('/grupos/{grupo}/entrar', [GrupoCaronaController::class, 'entrar'])->name('grupos.entrar');
    Route::delete('/grupos/{grupo}/sair', [GrupoCaronaController::class, 'sair'])->name('grupos.sair');
    Route::get('/home/', [HomePassageiroController::class, 'mostrar'])->name('home');
    Route::get('/perfil', fn () => view('usuario.perfil'))->name('usuario.perfil');
    Route::delete('/deletar-conta', function (Request $request, DeleteUser $deleter) {
        $deleter->delete($request->user());

        return redirect('/')->with('status', 'Conta deletada com sucesso.');
    })->name('usuario.deletar');

    Route::get('/carteira', [CarteiraController::class, 'exibir'])->name('usuario.carteira');
    Route::patch('/carteira', [CarteiraController::class, 'depositar'])->name('carteira.depositar');

    Route::post('/trajeto', [TrajetoController::class, 'store'])->name('trajeto.store');
    Route::get('/trajeto/{trajeto}', [TrajetoController::class, 'show'])->name('trajeto.show');
    Route::delete('/trajeto/{trajeto}', [TrajetoController::class, 'destroy'])->name('trajeto.destroy');
    Route::get('/trajeto/{trajeto}/rota', [TrajetoController::class, 'rota'])->name('trajeto.rota');
    Route::post('trajeto/{trajeto}/caronas/{pedidoCarona}', [TrajetoController::class, 'atenderPedidoCarona'])->name('trajeto.criar-carona');
    Route::get('/trajeto/{trajeto}/sugestoes-carona', [TrajetoController::class, 'sugestoesCarona'])->name('trajeto.sugestoes-carona');
    Route::post('/trajeto/{trajeto}/iniciar', [TrajetoController::class, 'iniciar'])->name('trajeto.iniciar');
    Route::post('/trajeto/{trajeto}/finalizar', [TrajetoController::class, 'finalizar'])->name('trajeto.finalizar');
    Route::post('/trajeto/{trajeto}/embarcar/{carona}', [TrajetoController::class, 'embarcar'])->name('trajeto.embarcar-carona');
    Route::delete('/trajeto/{trajeto}/caronas/{carona}', [TrajetoController::class, 'cancelarCarona'])->name('trajeto.cancelar-carona');

    Route::post('/pedido-carona', [PedidoCaronaController::class, 'store'])->name('pedido-carona.store');
    Route::get('/pedido-carona/estimativa', [PedidoCaronaController::class, 'estimativa'])->name('pedido-carona.estimativa');
    Route::get('/pedido-carona/{pedidoCarona}', [PedidoCaronaController::class, 'show'])->name('pedido-carona.show');
    Route::delete('/pedido-carona/{pedidoCarona}', [PedidoCaronaController::class, 'destroy'])->name('pedido-carona.destroy');

    Route::get('/atividade', [TransacaoController::class, 'index'])->name('transacao.index');
});
