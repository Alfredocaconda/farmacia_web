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
// ===============================
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

    // ================= VENDAS =================
    Route::get('/vendas', [VendaController::class, 'index'])->name('vendas.index');
    Route::post('/vendas/adicionar', [VendaController::class, 'adicionar_carrinho'])->name('vendas.add');
    Route::get('/vendas/remover/{id}', [VendaController::class, 'removeFromCart'])->name('vendas.remove');
    Route::get('/vendas/limpar', [VendaController::class, 'apagar_carrinho'])->name('vendas.clear');
    Route::post('/vendas/finalizar', [VendaController::class, 'store'])->name('vendas.store');

    Route::get('/vendas/relatorio', [VendaController::class, 'relatorio'])->name('vendas.relatorio');
    Route::get('/vendas/imprimir/{codigo_fatura}', [VendaController::class, 'imprimir'])->name('vendas.imprimir');
    Route::get('/vendas/relatorio/pdf', [VendaController::class, 'exportarPDF'])->name('vendas.relatorio.pdf');

    // ================= DEVOLUÇÕES =================
    Route::get('/devolucoes', [VendaController::class, 'devolucao'])->name('devolucoes.devolucao');
    Route::delete('/devolucoes/{id}', [VendaController::class, 'eliminarVenda'])->name('devolucoes.eliminar');

});