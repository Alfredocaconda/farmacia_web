<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use App\Models\Stock;
use App\Models\Venda;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $expirados = Stock::whereDate('caducidade', '<', now())->count();

        $criticos = Stock::whereBetween('caducidade', [
            now(),
            now()->addDays(5)
        ])->count();
     

            $lucro = DB::table('vendas')
                ->join('stocks', 'vendas.stock_id', '=', 'stocks.id')
                ->select(DB::raw('SUM((vendas.preco_unitario - stocks.preco) * vendas.quantidade) as total'))
                ->value('total');

               // Stock baixo (<= 5)
            $stockBaixo = Stock::with('produto')
                ->where('qtd_stock', '<=', 5)
                ->get();

            $totalStockBaixo = $stockBaixo->count();

        return view('pages.admin.index', compact('expirados', 'criticos','lucro',
        'stockBaixo', 'totalStockBaixo'));
    }
    
    public function data()
    {
        return response()->json([
            'funcionarios' => Funcionario::count(),
            'stock' => Stock::sum('qtd_stock'),
            'vendas' => Venda::sum('quantidade'),
            'caducados' => Stock::whereDate('caducidade','<',now())->count(),
        ]);
    }
}