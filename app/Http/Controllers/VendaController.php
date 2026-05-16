<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\produto;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class VendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
        $search = $request->input('search');

        $stocks = Stock::with('produto')
            ->where('qtd_stock', '>', 0)
            ->when($search, function ($query, $search) {
                return $query->whereHas('produto', function ($q) use ($search) {
                    $q->where('nome', 'like', '%' . $search . '%');
                    $q->orwhere('codigo_barra', 'like', '%' . $search . '%');
                });
            })
            ->get();

        $cart = Session::get('cart', []);

        $limiteMinimo = 5;

        // Produtos com baixo stock
        $baixoStock = Stock::with('produto')
            ->where('qtd_stock', '<=', $limiteMinimo)
            ->get();

        $totalBaixoStock = $baixoStock->count();
        return view('pages.venda', compact('baixoStock', 'totalBaixoStock','stocks', 'cart'));
    }
    
    /**
     * Display a listing of the resource.
     */
    public function adicionar_carrinho(Request $request)
    {
        $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'quantidade' => 'required|integer|min:1',
        ]);

        $stock = Stock::with('produto')->findOrFail($request->stock_id);
        $id = $stock->id;
        $quantidade = $request->quantidade;

        if ($quantidade > $stock->qtd_stock) {
            return back()->with('ERRO', 'Quantidade solicitada é maior que o estoque.');
        }

        $cart = Session::get('cart', []);
        if (isset($cart[$id])) {
            $cart[$id]['quantidade'] += $quantidade;
        } else {
            $cart[$id] = [
                'id' => $id,
                'nome' => $stock->produto->nome,
                'preco' => $stock->preco,
                'quantidade' => $quantidade,
            ];
        }

        Session::put('cart', $cart);
        return back();
    }
    /**
     * Display a listing of the resource.
     */
    public function removeFromCart($id)
    {
        $cart = Session::get('cart', []);
        unset($cart[$id]);
        Session::put('cart', $cart);
        return back();
    }
    /**
     * Display a listing of the resource.
     */
    public function apagar_carrinho()
    {
        Session::forget('cart');
        return back();
    }
    /**
     * Display a listing of the resource.
     */
    public function store(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->back()->with('ERRO', 'O carrinho está vazio.');
        }

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['preco'] * $item['quantidade'];
        }

        $valor_entregue = $request->valor_entregue;

        if ($valor_entregue < $total) {
            return redirect()->back()->with('ERRO', 'O valor entregue é menor que o total da compra.');
        }

        $troco = $valor_entregue - $total;

        $anoAtual = Carbon::now()->year;

        // Buscar o último código de venda do ano atual
        $ultimaVenda = DB::table('vendas')
            ->whereYear('data_venda', $anoAtual)
            ->orderByDesc('id')
            ->first();

        if ($ultimaVenda && preg_match('/^' . $anoAtual . '-(\d{4})$/', $ultimaVenda->codigo_fatura, $matches)) {
            $numeroSequencial = (int) $matches[1] + 1;
        } else {
            $numeroSequencial = 1;
        }

        $codigo_fatura = $anoAtual . '-' . str_pad($numeroSequencial, 4, '0', STR_PAD_LEFT);

        try {
            foreach ($cart as $item) {
                $subtotal = $item['preco'] * $item['quantidade'];

                // Registra a venda
                $venda = new Venda();
                $venda->codigo_fatura = $codigo_fatura;
                $venda->stock_id = $item['id'];
                $venda->quantidade = $item['quantidade'];
                $venda->preco_unitario = $item['preco'];
                $venda->subtotal = $subtotal;
                $venda->cliente = $request->cliente;
                $venda->forma_pagamento = $request->forma_pagamento;
                $venda->data_venda = now();
                $venda->funcionario_id = $request->id_funcionario;
                $venda->save();

                // Atualiza o estoque
                $stock = Stock::find($item['id']);
                if ($stock) {
                    $stock->qtd_stock -= $item['quantidade'];
                    $stock->save();
                }
            }
             // Armazena valor entregue e troco na sessão para exibir depois na fatura
            session([
                'valor_entregue_' . $codigo_fatura => $valor_entregue,
                'troco_' . $codigo_fatura => $troco,
            ]);
            // Limpa o carrinho da sessão
            session()->forget('cart');

            // Redireciona para a rota que mostra a fatura
            return redirect()->route('vendas.imprimir', ['codigo_fatura' => $codigo_fatura]);

        } catch (\Exception $e) {
            return redirect()->back()->with('ERRO', 'Erro ao finalizar venda: ' . $e->getMessage());
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function relatorio(Request $request)
    {
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $pesquisa = $request->input('pesquisa');

        // Se nada for inserido, retorna uma coleção vazia
        if (!$dataInicio && !$dataFim && !$pesquisa) {
            $vendas = collect();
            $totalGeral = 0;
        } else {
            $query = Venda::with(['produto', 'funcionario']);

            // Filtro por data (sem hora)
            if ($dataInicio && $dataFim) {
                $query->whereBetween('data_venda', [$dataInicio, $dataFim]);
            }

            // Se houver texto pesquisado (produto ou funcionário)
            if ($pesquisa) {
                $query->where(function ($q) use ($pesquisa) {
                    $q->whereHas('funcionario', function ($q2) use ($pesquisa) {
                        $q2->where('nome', 'like', '%' . $pesquisa . '%');
                    })->orWhereHas('produto', function ($q3) use ($pesquisa) {
                        $q3->where('nome', 'like', '%' . $pesquisa . '%');
                    });
                });
            }

            $vendas = $query->orderBy('data_venda', 'desc')->get();
            $totalGeral = $vendas->sum('subtotal');
        }

        return view('pages.admin.relatorio', compact('vendas', 'totalGeral', 'dataInicio', 'dataFim', 'pesquisa'));
    }
    /**
     * Display a listing of the resource.
     */
    public function exportarPDF(Request $request)
    {
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $pesquisa = $request->input('pesquisa');

        if (!$dataInicio && !$dataFim && !$pesquisa) {
            $vendas = collect();
            $totalGeral = 0;
        } else {
            $query = Venda::with(['produto', 'funcionario']);

            if ($dataInicio && $dataFim) {
                $query->whereBetween('data_venda', [$dataInicio, $dataFim]);
            }

            if ($pesquisa) {
                $query->where(function ($q) use ($pesquisa) {
                    $q->whereHas('funcionario', function ($q2) use ($pesquisa) {
                        $q2->where('nome', 'like', '%' . $pesquisa . '%');
                    })->orWhereHas('produto', function ($q3) use ($pesquisa) {
                        $q3->where('nome', 'like', '%' . $pesquisa . '%');
                    });
                });
            }

            $vendas = $query->orderBy('data_venda', 'desc')->get();
            $totalGeral = $vendas->sum('subtotal');
        }

        $pdf = Pdf::loadView('pages.admin.relatorio_pdf', compact('vendas', 'totalGeral', 'dataInicio', 'dataFim', 'pesquisa'))
                ->setPaper('A4', 'portrait');

        return $pdf->download('relatorio_vendas.pdf');
    }
    /**
     * Display a listing of the resource.
     */
    public function devolucao(Request $request)
    {
        $codigo_fatura = $request->input('codigo_fatura');
        // Retorna uma collection vazia
        $vendas = collect();

        if ($codigo_fatura) {
            $vendas = Venda::with(['produto.stock'])
                        ->where('codigo_fatura', $codigo_fatura)
                        ->get();
        }

        return view('pages.admin.devolucoes', compact('vendas', 'codigo_fatura'));
    }
    /**
     * Display a listing of the resource.
     */
    public function eliminarVenda(Request $request, $id)
    {
        DB::transaction(function () use ($id) {
            $venda = Venda::with('produto.stock')->findOrFail($id);

            // Atualiza o stock
            if ($venda->produto && $venda->produto->stock) {
                $venda->produto->stock->qtd_stock += $venda->quantidade;
                $venda->produto->stock->save();
            }

            // Deleta a venda
            $venda->delete();
        });

        return redirect()->back()->with('success', 'Produto devolvido e removido da venda com sucesso.');
    }
     /**
     * Display a listing of the resource.
     */
    public function imprimir($codigo_fatura)
    {
        $vendas = Venda::with(['produto', 'funcionario'])
            ->where('codigo_fatura', $codigo_fatura)
            ->get();

        $troco = request()->query('troco', 0);

        $funcionario = $vendas->first()->funcionario ?? null;
        $valor_entregue = session('valor_entregue_' . $codigo_fatura);
        $troco = session('troco_' . $codigo_fatura);

        return view('pages.factura.imprimir', [
            'vendas' => $vendas,
            'funcionario' => $funcionario,
            'valor_entregue' => $valor_entregue,
            'troco' => $troco,
        ]);
    }
}
