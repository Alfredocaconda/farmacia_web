@extends('layouts.base')
@section('title', 'VENDAS')

@section('content')

<div class="container-fluid py-3">

    {{-- ================= HEADER ================= --}}
    <div class="card mb-3 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center">

            <div class="d-flex align-items-center">
                <img src="{{ asset('images/logo.png') }}" style="height:45px; margin-right:10px;">
                <div>
                    <h5 class="mb-0">Farmácia 4 de Setembro</h5>
                    <small class="text-muted">Sistema de Vendas</small>
                </div>
            </div>

           <div class="text-end">

                <strong>{{ Auth::guard('funcionario')->user()->nome }}</strong><br>

                <span class="badge bg-info">
                    {{ Auth::guard('funcionario')->user()->funcao }}
                </span><br>

                <small>{{ now()->format('d/m/Y H:i') }}</small><br>

                {{-- BOTÃO SAIR --}}
                @php
                    $user = Auth::guard('funcionario')->user();
                @endphp

                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf

                    <button type="submit"
                            class="btn btn-sm btn-danger mt-1">

                        Sair
                    </button>
                </form>

            </div>

        </div>
    </div>

    <div class="row">

        {{-- ================= PRODUTOS ================= --}}
        <div class="col-md-7">

            {{-- PESQUISA COMPACTA --}}
            <div class="card mb-2">
                <div class="card-body p-2">
                    <form method="GET">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search"
                                class="form-control"
                                placeholder="Pesquisar produto..."
                                value="{{ request('search') }}">
                            <button class="btn btn-primary">🔍</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TABELA COMPACTA --}}
            <div class="card">
                <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Produto</th>
                                <th>Preço</th>
                                <th>Quantidade</th>
                                <th>Opções</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($stocks as $stock)

                            @php
                                $expirado = \Carbon\Carbon::parse($stock->caducidade)->isPast();
                                if($expirado) continue;
                            @endphp

                            <tr>
                                <td>{{ $stock->produto->nome }}</td>
                                <td>{{ number_format($stock->preco_venda,2) }} Kz</td>
                                <td>
                                    <span class="badge {{ $stock->qtd_stock < 10 ? 'bg-warning':'bg-success' }}">
                                        {{ $stock->qtd_stock }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary"
                                        onclick="abrirModal({{ $stock->id }}, '{{ $stock->produto->nome }}', {{ $stock->qtd_stock }})">
                                        +
                                    </button>
                                </td>
                            </tr>

                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>

        </div>

        {{-- ================= CARRINHO ================= --}}
            {{-- ================= CARRINHO ERP ================= --}}
        <div class="col-md-5">

            <div class="card shadow-sm">

                {{-- HEADER --}}
                <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
                    <span>🛒 Carrinho</span>
                    <a href="{{ route('vendas.clear') }}" class="btn btn-sm btn-light">Limpar</a>
                </div>

                <div class="card-body p-2">

                    @php $total = 0; @endphp

                    @if(empty($cart))
                        <div class="text-center text-muted py-3">
                            <small>Sem produtos</small>
                        </div>
                    @else

                        {{-- LISTA COMPACTA --}}
                        <div style="max-height:250px; overflow-y:auto;">

                            @foreach($cart as $item)

                            @php
                                $subtotal = $item['preco_venda'] * $item['quantidade'];
                                $total += $subtotal;
                            @endphp

                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">

                                {{-- PRODUTO --}}
                                <div style="width:45%">
                                    <small class="fw-bold">{{ $item['nome'] }}</small>
                                    <br>
                                    <small class="text-muted">
                                        {{ number_format($item['preco_venda'],2) }} Kz
                                    </small>
                                </div>

                                {{-- CONTROLO QTD --}}
                                <div class="d-flex align-items-center">

                                    <a href="{{ route('vendas.diminuir',$item['id']) }}"
                                    class="btn btn-sm btn-outline-secondary px-2">−</a>

                                    <span class="mx-2">{{ $item['quantidade'] }}</span>

                                    <a href="{{ route('vendas.aumentar',$item['id']) }}"
                                    class="btn btn-sm btn-outline-secondary px-2">+</a>
                                </div>

                                {{-- SUBTOTAL --}}
                                <div style="width:25%" class="text-end">
                                    <small class="fw-bold">
                                        {{ number_format($subtotal,2) }} Kz
                                    </small>
                                </div>

                                {{-- REMOVER --}}
                                <a href="{{ route('vendas.remove',$item['id']) }}"
                                class="btn btn-sm btn-danger ms-2">x</a>

                            </div>

                            @endforeach

                        </div>

                        {{-- TOTAL FIXO --}}
                        <div class="mt-3 p-2 bg-light rounded text-center">
                            <h5 class="mb-0">
                                Total: <strong>{{ number_format($total,2) }} Kz</strong>
                            </h5>
                        </div>

                        {{-- PAGAMENTO SIMPLES ERP --}}
                        <form action="{{ route('vendas.store') }}" method="POST" class="mt-2">
                            @csrf

                            <input type="hidden" name="id_funcionario"
                                value="{{ Auth::guard('funcionario')->user()->id }}">

                            <div class="row g-2">

                                <div class="col-6">
                                    <select name="forma_pagamento" class="form-control form-control-sm" required>
                                        <option value="dinheiro">Dinheiro</option>
                                        <option value="multicaixa">Multicaixa</option>
                                    </select>
                                </div>

                                <div class="col-6">
                                        <input type="number"
                                            name="valor_entregue"
                                            id="valor_entregue"
                                            class="form-control form-control-sm"
                                            placeholder="Valor"
                                            min="{{ $total }}"
                                            required>
                                </div>

                            </div>

                            <input type="text"
                                id="troco"
                                class="form-control form-control-sm mt-2 bg-light text-center"
                                placeholder="Troco"
                                readonly>

                            <button class="btn btn-success btn-sm w-100 mt-2">
                                Finalizar Venda
                            </button>

                        </form>

                    @endif

                </div>
            </div>
        </div>

    </div>

    {{-- ================= RODAPÉ ================= --}}
    <div class="text-center mt-4 text-muted">
        <small>FARMÁCIA 4 DE SETEMBRO © {{ date('Y') }} | Sistema Profissional</small>
    </div>

</div>

{{-- ================= MODAL ================= --}}
<div id="modalVenda" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:10px; width:300px;">

        <h5 id="produtoNome"></h5>

        <form method="POST" action="{{ route('vendas.add') }}">
            @csrf

            <input type="hidden" name="stock_id" id="stock_id">

            <input type="number" name="quantidade"
                id="quantidade"
                class="form-control mb-2"
                placeholder="Quantidade">

            <button class="btn btn-success w-100">Adicionar</button>
        </form>

        <button class="btn btn-secondary w-100 mt-2"
            onclick="fecharModal()">Cancelar</button>

    </div>
</div>

{{-- ================= JS ================= --}}
<script>

function abrirModal(id,nome,max){
    document.getElementById('modalVenda').style.display='flex';
    document.getElementById('stock_id').value=id;
    document.getElementById('produtoNome').innerText=nome;
    document.getElementById('quantidade').max=max;
}

function fecharModal(){
    document.getElementById('modalVenda').style.display='none';
}

// TROCO
document.getElementById('valor_entregue')?.addEventListener('input',function(){

    let total = {{ $total ?? 0 }};
    let valor = parseFloat(this.value);

    if(!isNaN(valor)){
        let troco = valor - total;

        document.getElementById('troco').value =
            troco >= 0 ? troco.toFixed(2)+' Kz' : 'Insuficiente';
    }
});

</script>

@endsection