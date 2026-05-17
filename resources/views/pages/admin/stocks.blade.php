@extends('layouts.app')
@section('title', 'STOCK')
@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Gestão de Stock</h4>
        </div>
    </div>

    {{-- ALERTAS --}}
    @if(session('ERRO'))
        <div class="alert alert-danger">{{ session('ERRO') }}</div>
    @endif

    @if(session('SUCESSO'))
        <div class="alert alert-success">{{ session('SUCESSO') }}</div>
    @endif

    {{-- ALERTA STOCK BAIXO --}}
    @if($totalBaixoStock > 0)
        <div class="alert alert-warning">
            <strong>⚠️ Stock Baixo:</strong>
            <ul class="mb-0">
                @foreach($baixoStock as $item)
                    <li>{{ $item->produto->nome }} — <strong>{{ $item->qtd_stock }}</strong></li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- TABELA --}}
    <div class="card">
        <div class="card-body table-responsive">

            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Produto</th>
                        <th>Compra</th>
                        <th>Venda</th>
                        <th>Qtd</th>
                        <th>Fornecedor</th>
                        <th>Validade</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($stock as $dados)
                        <tr class="
                            {{ $dados->alerta == 'expirado' ? 'table-danger' : '' }}
                            {{ $dados->alerta == 'critico' ? 'table-warning' : '' }}
                        ">

                            <td>{{ $dados->produto->nome }}</td>
                            <td>{{ $dados->preco_compra }} KZ</td>
                            <td>{{ $dados->preco_venda }} KZ</td>
                            <td><strong>{{ $dados->qtd_stock }}</strong></td>
                            <td>{{ $dados->fornecedor }}</td>
                            <td>{{ $dados->caducidade }}</td>

                            <td>
                                @if($dados->alerta_completa == 'critico_total')
                                    <span class="badge bg-danger">⚠ CRÍTICO TOTAL</span>

                                @elseif($dados->alerta_completa == 'expirado')
                                    <span class="badge bg-dark">⛔ EXPIRADO</span>

                                @elseif($dados->alerta_completa == 'baixo_stock')
                                    <span class="badge bg-warning">📦 STOCK BAIXO</span>

                                @elseif($dados->alerta_completa == 'critico_validade')
                                    <span class="badge bg-danger">⏰ VALIDADE CRÍTICA</span>

                                @elseif($dados->alerta_completa == 'atencao_validade')
                                    <span class="badge bg-info">⚠ ATENÇÃO VALIDADE</span>

                                @else
                                    <span class="badge bg-success">NORMAL</span>
                                @endif
                            </td>

                            <td>

                                <button class="btn btn-sm btn-success"
                                    data-toggle="modal"
                                    data-target="#AumentarStock"
                                    onclick="abrirModalAumentar({{ $dados->id }}, '{{ $dados->produto->nome }}')">
                                    + Stock
                                </button>
                               
                                <button class="btn btn-sm btn-info"
                                    data-toggle="modal"
                                    data-target="#Cadastrar"
                                    onclick='editar(@json($dados))'>
                                    Editar
                                </button>

                                <a href="{{ route('stock.destroy',$dados->id) }}"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Eliminar este stock?')">
                                    Apagar
                                </a>

                            </td>

                        </tr>
                    @endforeach
                </tbody>

            </table>

        </div>
    </div>
</div>

{{-- ========================= --}}
{{-- MODAL CADASTRAR / EDITAR --}}
{{-- ========================= --}}
<div class="modal fade" id="Cadastrar">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Entrada de Stock</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form action="{{ route('stock.store') }}" method="POST">
                @csrf

                <input type="hidden" name="id" id="id">
                <input type="hidden" name="id_funcionario" value="{{ Auth::guard('funcionario')->user()->id }}">
                <input type="hidden" name="id_produto" id="id_produto">

                <div class="modal-body">

                    <p><strong>Produto:</strong>
                        <span id="nomeProdutoSelecionado">Selecione um produto</span>
                    </p>

                    <div class="row">

                        <x-input-normal id="preco_compra" name="preco_compra" type="number" titulo="Preço Compra" />
                        <x-input-normal id="preco_venda" name="preco_venda" type="number" titulo="Preço Venda" />
                        <x-input-normal id="qtd_stock" name="qtd_stock" type="number" titulo="Quantidade" />
                        <x-input-normal id="fornecedor" name="fornecedor" type="text" titulo="Fornecedor" />
                        <x-input-normal id="codigo_barra" name="codigo_barra" type="text" titulo="Código de Barra" />
                        <x-input-normal id="caducidade" name="caducidade" type="date" titulo="Validade" />

                    </div>

                </div>

                <div class="modal-footer">
                    <x-botao-form />
                </div>

            </form>

        </div>
    </div>
</div>

{{-- ========================= --}}
{{-- MODAL AUMENTAR STOCK --}}
{{-- ========================= --}}
<div class="modal fade" id="AumentarStock" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="{{ route('stock.store') }}" method="POST">
                @csrf

                <input type="hidden" name="id" id="stock_id_aumentar">
                <input type="hidden" name="tipo" value="aumentar">

                <div class="modal-header">
                    <h5 class="modal-title">Aumentar Stock</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <p><strong>Produto:</strong>
                        <span id="nomeProdutoModal"></span>
                    </p>

                    <div class="form-group">
                        <label>Quantidade a Adicionar</label>
                        <input type="number" name="qtd_stock" class="form-control" required min="1">
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Aumentar</button>
                </div>

            </form>

        </div>
    </div>
</div>

{{-- ========================= --}}
{{-- JAVASCRIPT ERP --}}
{{-- ========================= --}}
<script>

function editar(dados) {
    $('#id').val(dados.id);
    $('#preco_compra').val(dados.preco_compra);
    $('#preco_venda').val(dados.preco_venda);
    $('#fornecedor').val(dados.fornecedor);
    $('#codigo_barra').val(dados.codigo_barra);
    $('#qtd_stock').val(dados.qtd_stock);
    $('#caducidade').val(dados.caducidade);
    $('#id_produto').val(dados.id_produto);

    if (dados.produto) {
        $('#nomeProdutoSelecionado').text(
            dados.produto.nome + " / " + dados.produto.descricao
        );
    }
}

function limpar() {
    $('#id').val('');
    $('#qtd_stock').val('');
    $('#fornecedor').val('');
    $('#codigo_barra').val('');
    $('#preco_compra').val('');
    $('#preco_venda').val('');
    $('#caducidade').val('');
    $('#id_produto').val('');
    $('#nomeProdutoSelecionado').text('Selecione um produto');
}

function abrirModalAumentar(id, nome) {
    $('#stock_id_aumentar').val(id);
    $('#nomeProdutoModal').text(nome);
}

</script>

@endsection