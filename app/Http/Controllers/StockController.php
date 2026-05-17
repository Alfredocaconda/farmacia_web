<?php

namespace App\Http\Controllers;

use App\Models\stock;
use App\Models\produto;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Nette\Schema\ValidationException;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
        public function index($id = null){
                if ($id) {
                    // Buscar um único produto e seus stocks
                    $valor = produto::orderby('nome','desc')->findOrFail($id);
                    $stock = stock::with('produto', 'funcionario')->where('id_produto', $id)->get();
                } else {
                    // Buscar todos os produtos e todos os stocks
                    $valor = null;
                    $stock = stock::with('produto', 'funcionario')->get();
                }
                    // Verificar dias restantes para validade
                    foreach ($stock as $item) {

                        if ($item->caducidade) {
                            $dias = Carbon::now()->diffInDays($item->caducidade, false);

                            if ($dias <= 0) {
                                $item->alerta = 'expirado';
                            } elseif ($dias <= 5) {
                                $item->alerta = 'critico';
                            } elseif ($dias <= 10) {
                            $item->alerta = 'atencao';
                            } else {
                                $item->alerta = 'normal';
                            }
                        } else {
                            $item->alerta = 'sem_validade';
                        }
                    }
                    $limiteMinimo = 5;

                // Produtos com baixo stock
                $baixoStock = Stock::with('produto')
                    ->where('qtd_stock', '<=', $limiteMinimo)
                    ->get();

                $totalBaixoStock = $baixoStock->count();
            return view('pages.admin.stocks', compact('stock', 'valor', 'baixoStock', 'totalBaixoStock'));
        }
       /**
     * Remove the specified resource from storage.
     */
    public function view($id){
        $valor=produto::findOrFail($id);
      return view('pages.admin.stocks',compact('valor'));  
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

           // =========================
            // CASE 1: AUMENTAR STOCK
            // =========================
            if ($request->filled('id') && $request->tipo === 'aumentar') {

                    $request->validate([
                        'qtd_stock' => 'required|integer|min:1'
                    ]);

                    return $this->aumentarStock($request);
            }
            // =========================
            // VALIDATION
            // =========================
            $request->validate([
                'fornecedor' => ['required', 'string', 'regex:/^[a-zA-ZÀ-ÿ\s]+$/'],
                'preco_compra' => ['required', 'numeric'],
                'preco_venda' => ['required', 'numeric'],
                'codigo_barra' => ['required', 'string'],
                'qtd_stock' => ['required', 'integer'],
                'caducidade' => ['required', 'date'],
            ], [
                'fornecedor.required' => 'O fornecedor é obrigatório!',
                'fornecedor.regex' => 'O nome do fornecedor deve conter apenas letras!',
                'codigo_barra.required' => 'Código de barra é obrigatório!',
                'qtd_stock.required' => 'Quantidade é obrigatória!',
                'caducidade.required' => 'Caducidade é obrigatória!',
                'preco_compra.required' => 'Preço de compra é obrigatório!',
                'preco_venda.required' => 'Preço de venda é obrigatório!',
            ]);

           

            // =========================
            // CASE 2: EDITAR STOCK
            // =========================
            if ($request->filled('id')) {
                return $this->atualizarStock($request);
            }

            // =========================
            // CASE 3: NOVA ENTRADA
            // =========================
            return $this->criarOuAtualizarStock($request);

        } catch (ValidationException $e) {
            return redirect()->route('stock.index')
                ->withErrors($e->validator)
                ->withInput();

        }
        
         catch (QueryException $e) {
            return redirect()->route('produto.index')
                ->with("ERRO", "Erro ao processar stock.");
        }
    }
    // =========================
    // FUNÇÕES AUXILIARES
    // =========================
    private function preencherStock($stock, $request)
    {
        $stock->id_funcionario = $request->id_funcionario;
        $stock->id_produto = $request->id_produto;
        $stock->preco_compra = $request->preco_compra;
        $stock->preco_venda = $request->preco_venda;
        $stock->qtd_stock = $request->qtd_stock;
        $stock->fornecedor = $request->fornecedor;
        $stock->codigo_barra = $request->codigo_barra;
        $stock->caducidade = $request->caducidade;
        $stock->data_entrada = now();
    }
    // =========================
    // FUNÇÕES AUXILIARES
    // =========================
    private function criarOuAtualizarStock($request)
    {
        $stock = Stock::where('id_produto', $request->id_produto)->first();

        if ($stock) {

            // ERP RULE: produto já existe → atualizar stock
            $stock->qtd_stock += $request->qtd_stock;

            $this->preencherStock($stock, $request);

            $stock->save();

            return redirect()->route('stock.index')
                ->with("SUCESSO", "Stock atualizado automaticamente.");

        }

        // novo produto no stock
        $stock = new Stock();

        $this->preencherStock($stock, $request);

        $stock->save();

        return redirect()->route('produto.index')
            ->with("SUCESSO", "Stock criado com sucesso.");
    }
    // =========================
    // FUNÇÕES AUXILIARES
    // =========================
    private function atualizarStock($request)
    {
        $stock = Stock::find($request->id);

        if (!$stock) {
            return redirect()->route('stock.index')
                ->with("ERRO", "Stock não encontrado.");
        }

        $this->preencherStock($stock, $request);

        $stock->save();

        return redirect()->route('stock.index')
            ->with("SUCESSO", "Stock atualizado com sucesso.");
    }
    // =========================
    // FUNÇÕES AUXILIARES
    // =========================
    private function aumentarStock($request)
    {
        $stock = Stock::find($request->id);

        if (!$stock) {
            return redirect()->route('stock.index')
                ->with("ERRO", "Stock não encontrado.");
        }

        $stock->qtd_stock += $request->qtd_stock;
        $stock->save();

        return redirect()->route('stock.index')
            ->with("SUCESSO", "Quantidade aumentada com sucesso.");
    }
    /**
     * Display the specified resource.
     */

    public function check($id)
    {
        return response()->json([
            'existe' => Stock::where('id_produto', $id)->exists()
        ]);
    }

 

    public function show($id)
    {
        //
    $valor=stock::find($id);
    if (!$valor) {
        # code...
        return redirect()->back()->with("ERRO","FUNCIONÁRIO NÃO ENCONTRADO");
    } 
    return view('pages.admin.stocks',compact('valor'));
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $valor=stock::find($id);
        if(!$valor){
            return redirect()->back()->with("ERRO","STOCK NÃO ENCONTRADO");
        }
        $valor->delete();
        return redirect()->back()->with("SUCESSO","STOCK APAGADO COM SUCESSO");
    } 
}
