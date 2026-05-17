<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VendaController extends Controller
{
    /**
     * =========================
     * LISTAR PRODUTOS (POS)
     * =========================
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $stocks = Stock::with('produto')
            ->where('qtd_stock', '>', 0)
            ->when($search, function ($query) use ($search) {
                $query->whereHas('produto', function ($q) use ($search) {
                    $q->where('nome', 'like', "%$search%")
                      ->orWhere('codigo_barra', 'like', "%$search%");
                });
            })
            ->get();

        $cart = Session::get('cart', []);

        return view('pages.venda', compact('stocks', 'cart'));
    }

    /**
     * =========================
     * ADICIONAR AO CARRINHO
     * =========================
     */
    public function add(Request $request)
    {
        $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'quantidade' => 'required|integer|min:1'
        ]);

        $stock = Stock::with('produto')->findOrFail($request->stock_id);

        if ($stock->caducidade && Carbon::parse($stock->caducidade)->isPast()) {
            return back()->with('ERRO', 'Produto expirado.');
        }

        if ($request->quantidade > $stock->qtd_stock) {
            return back()->with('ERRO', 'Stock insuficiente.');
        }

        $cart = Session::get('cart', []);

        if (isset($cart[$stock->id])) {
            $cart[$stock->id]['quantidade'] += $request->quantidade;
        } else {
            $cart[$stock->id] = [
                'id' => $stock->id,
                'nome' => $stock->produto->nome,
                'preco_venda' => $stock->preco_venda,
                'quantidade' => $request->quantidade
            ];
        }

        Session::put('cart', $cart);

        return back()->with('SUCESSO', 'Produto adicionado.');
    }

    /**
     * =========================
     * REMOVER ITEM
     * =========================
     */
    public function remove($id)
    {
        $cart = Session::get('cart', []);
        unset($cart[$id]);
        Session::put('cart', $cart);

        return back();
    }

    /**
     * =========================
     * LIMPAR CARRINHO
     * =========================
     */
    public function clear()
    {
        Session::forget('cart');
        return back();
    }

    /**
     * =========================
     * FINALIZAR VENDA (ERP CORE)
     * =========================
     */
    public function store(Request $request)
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return back()->with('ERRO', 'Carrinho vazio.');
        }

        $total = collect($cart)->sum(function ($item) {
            return $item['preco_venda'] * $item['quantidade'];
        });

        // 🔥 LIMPEZA FORTE DO VALOR
        $valor_entregue = $request->valor_entregue;

        $valor_entregue = str_replace([' ', 'Kz', ','], ['', '', '.'], $valor_entregue);
        $valor_entregue = (float) preg_replace('/[^0-9.]/', '', $valor_entregue);

        // 🔥 DEBUG TEMPORÁRIO (PODE DEIXAR 1 TESTE)
        // dd($valor_entregue, $total);

        if ($valor_entregue < $total) {
            return back()->with('ERRO', "Valor insuficiente. Total: $total / Pago: $valor_entregue");
        }

        $troco = $valor_entregue - $total;

        DB::beginTransaction();

        try {

            $codigo = $this->gerarCodigoFatura();

            foreach ($cart as $item) {

                $stock = Stock::find($item['id']);

                if (!$stock || $stock->qtd_stock < $item['quantidade']) {
                    throw new \Exception("Stock insuficiente para {$item['nome']}");
                }

                Venda::create([
                    'codigo_fatura' => $codigo,
                    'stock_id' => $stock->id,
                    'quantidade' => $item['quantidade'],
                    'preco_venda' => $item['preco_venda'],
                    'subtotal' => $item['preco_venda'] * $item['quantidade'],
                    'cliente' =>"Cliente",
                    'forma_pagamento' => $request->forma_pagamento,
                    'data_venda' => now(),
                    'funcionario_id' => $request->id_funcionario
                ]);

                $stock->qtd_stock -= $item['quantidade'];
                $stock->save();
            }

            DB::commit();

            session([
                "valor_entregue_$codigo" => $valor_entregue,
                "troco_$codigo" => $troco
            ]);

            Session::forget('cart');

            return redirect()->route('vendas.imprimir', $codigo);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('ERRO', $e->getMessage());
        }
    }

    /**
     * =========================
     * GERAR FATURA
     * =========================
     */
    private function gerarCodigoFatura()
    {
        $ano = date('Y');

        $ultima = Venda::whereYear('data_venda', $ano)
            ->orderByDesc('id')
            ->first();

        $num = $ultima ? (int)substr($ultima->codigo_fatura, -4) + 1 : 1;

        return $ano . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * =========================
     * IMPRIMIR
     * =========================
     */
    public function imprimir($codigo)
    {
        $vendas = Venda::with(['stock.produto', 'funcionario'])
            ->where('codigo_fatura', $codigo)
            ->get();

        return view('pages.factura.imprimir', [
            'vendas' => $vendas,
            'valor_entregue' => session("valor_entregue_$codigo"),
            'troco' => session("troco_$codigo")
        ]);
    }

    /**
     * =========================
     * AUMENTAR QTD
     * =========================
     */
    public function aumentar($id)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantidade']++;
        }

        Session::put('cart', $cart);

        return back();
    }

    /**
     * =========================
     * DIMINUIR QTD
     * =========================
     */
    public function diminuir($id)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id]) && $cart[$id]['quantidade'] > 1) {
            $cart[$id]['quantidade']--;
        }

        Session::put('cart', $cart);

        return back();
    }
    public function relatorio()
    {
        $vendas = Venda::with(['stock.produto'])
            ->orderByDesc('created_at')
            ->get();

        return view('pages.admin.relatorio', compact('vendas'));
    }
    public function devolucao()
    {
        $vendas = Venda::with(['stock.produto', 'funcionario'])
            ->orderByDesc('created_at')
            ->get();

        return view('pages.admin.devolucoes', compact('vendas'));
    }
}