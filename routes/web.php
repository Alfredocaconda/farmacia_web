<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    FuncionarioController,
    ProdutoController,
    StockController,
    VendaController,
    FuncionarioAuthController,
    DashboardController,
    CategoriaController
};

// ===============================
// ROTAS PÚBLICAS
// ===============================yy
Route::get('/', [FuncionarioAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [FuncionarioAuthController::class, 'login'])->name('funcionario.login');
Route::post('/logout', [FuncionarioAuthController::class, 'logout'])->name('logout');


// ===============================
// ROTAS PRIVADAS (ERP)
// ===============================
Route::middleware(['auth'])->group(function () {

    // ================= DASHBOARD =================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

    // ================= FUNCIONÁRIO =================
    Route::resource('/funcionario', FuncionarioController::class);
    Route::post('/funcionario/store', [FuncionarioController::class,'store'])->name('funcionario.store');
    Route::get('/funcionario/destroy/{id}', [FuncionarioController::class,'destroy'])->name('funcionario.destroy');

    // ================= CATEGORIA =================
    Route::resource('/categoria', CategoriaController::class);
    Route::post('/categoria/store', [CategoriaController::class,'store'])->name('categoria.store');
    Route::get('/categoria/destroy/{id}', [CategoriaController::class,'destroy'])->name('categoria.destroy');

    // ================= PRODUTO =================
    Route::resource('/produto', ProdutoController::class);
    Route::post('/produto/store', [ProdutoController::class,'store'])->name('produto.store');
    Route::get('/produto/destroy/{id}', [ProdutoController::class,'destroy'])->name('produto.destroy');

    // ================= STOCK =================
    Route::post('/stock/store', [StockController::class, 'store'])->name('stock.store');
    Route::get('/stock/destroy/{id}', [StockController::class, 'destroy'])->name('stock.destroy');
    Route::get('/stocks/{id?}', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/check/{id}', [StockController::class, 'check']);

    // ================= VENDAS =================
    Route::prefix('vendas')->group(function () {

    Route::get('/', [VendaController::class, 'index'])
            ->name('vendas.index');

        Route::post('/adicionar', [VendaController::class, 'add'])
            ->name('vendas.add');

        Route::get('/remover/{id}', [VendaController::class, 'remove'])
            ->name('vendas.remove');

        Route::get('/limpar', [VendaController::class, 'clear'])
            ->name('vendas.clear');

        Route::post('/finalizar', [VendaController::class, 'store'])
            ->name('vendas.store');

        Route::get('/aumentar/{id}', [VendaController::class, 'aumentar'])
            ->name('vendas.aumentar');

        Route::get('/diminuir/{id}', [VendaController::class, 'diminuir'])
            ->name('vendas.diminuir');

        Route::get('/imprimir/{codigo_fatura}', [VendaController::class, 'imprimir'])
            ->name('vendas.imprimir');
    });
    Route::get('/vendas/relatorio', [VendaController::class, 'relatorio'])
    ->name('vendas.relatorio');
    /*
    |--------------------------------------------------------------------------
    | DEVOLUÇÕES
    |--------------------------------------------------------------------------
    */
   Route::prefix('devolucoes')->group(function () {
    Route::get('/', [VendaController::class, 'devolucao'])->name('devolucoes.index');
    Route::delete('/{id}', [VendaController::class, 'eliminarVenda'])->name('devolucoes.destroy');
});
});