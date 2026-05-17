@extends('layouts.app')
@section('title', 'Produtos')
@section('content')

<div class="container-fluid">

    {{-- HEADER ERP --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Gestão de Produtos</h4>

            <button class="btn btn-primary" data-toggle="modal" data-target="#produtoModal" onclick="novoProduto()">
                <i class="fa fa-plus"></i> Novo Produto
            </button>
        </div>
    </div>

    {{-- ALERTAS --}}
    @if (session('SUCESSO'))
        <div class="alert alert-success">{{ session('SUCESSO') }}</div>
    @endif

    @if (session('ERRO'))
        <div class="alert alert-danger">{{ session('ERRO') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-warning">
            <ul class="mb-0">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- TABELA ERP --}}
    <div class="card">
        <div class="card-body table-responsive">

            <table class="table table-hover table-striped" id="datatable">
                <thead class="thead-dark">
                    <tr>
                        <th>Produto</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Funcionário</th>
                        <th width="180">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($valor as $dados)
                        <tr>
                            <td>{{ $dados->nome }}</td>
                            <td>{{ $dados->descricao }}</td>
                            <td>{{ $dados->categoria->nome }}</td>
                            <td>{{ $dados->funcionario->nome }}</td>

                            {{-- ERP ACTIONS --}}
                            <td>
                                <button class="btn btn-sm btn-info"
                                    data-toggle="modal"
                                    data-target="#produtoModal"
                                    onclick='editarProduto(@json($dados))'>
                                    Editar
                                </button>

                                <button class="btn btn-sm btn-success"
                                    data-toggle="modal"
                                    data-target="#stockModal"
                                    onclick='verificarStock({{ $dados->id }}, "{{ $dados->nome }}")'>
                                    Stock
                                </button>

                                <a href="{{ route('produto.destroy',$dados->id) }}"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Eliminar este produto?')">
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

{{-- ===================== --}}
{{-- MODAL PRODUTO (CREATE/EDIT) --}}
{{-- ===================== --}}
<div class="modal fade" id="produtoModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="produtoModalTitle">Produto</h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>

            <form action="{{ route('produto.store') }}" method="POST">
                @csrf

                <input type="hidden" name="id" id="produto_id">

                <div class="modal-body row">

                    <x-input-normal id="nome" name="nome" type="text" titulo="Nome" />
                    <x-input-normal id="descricao" name="descricao" type="text" titulo="Descrição" />

                    <div class="col-md-12 mb-3">
                        <label>Categoria</label>
                        <select name="categoria_id" id="categoria" class="form-control">
                            <option value="">Selecionar</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}">
                                    {{ $categoria->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Guardar</button>
                </div>

            </form>

        </div>
    </div>
</div>

{{-- ===================== --}}
{{-- MODAL STOCK --}}
{{-- ===================== --}}
<div class="modal fade" id="stockModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Entrada de Stock</h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>

            <form action="{{ route('stock.store') }}" method="POST">
                @csrf

                <div class="modal-body">

                    <p><strong>Produto:</strong> <span id="stock_produto_nome"></span></p>

                    <input type="hidden" name="id_produto" id="stock_produto_id">
                    <input type="hidden" name="id_funcionario" value="{{ Auth::guard('funcionario')->user()->id }}">

                    <div class="row">
                        <x-input-normal id="preco_compra" name="preco_compra" type="number" titulo="Preço da Compra" />
                        <x-input-normal id="preco_venda" name="preco_venda" type="number" titulo="Preço de Venda" />
                        <x-input-normal id="qtd_stock" name="qtd_stock" type="number" titulo="Quantidade" />
                        <x-input-normal id="fornecedor" name="fornecedor" type="text" titulo="Fornecedor" />
                        <x-input-normal id="codigo_barra" name="codigo_barra" type="text" titulo="Código de Barra" />
                        <x-input-normal id="caducidade" name="caducidade" type="date" titulo="Validade" />
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Salvar Stock</button>
                </div>

            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="infoStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Stock Existente</h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <p id="stockMensagem"></p>
            </div>

            <div class="modal-footer">
                <a href="{{ route('stock.index') }}" class="btn btn-primary">
                    Ir para Stock
                </a>
                <button type="button" class="btn btn-secondary" onclick="fecharModais()">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ===================== --}}
{{-- JAVASCRIPT ERP --}}
{{-- ===================== --}}
<script>

    function novoProduto() {
        document.getElementById('produto_id').value = '';
        document.getElementById('nome').value = '';
        document.getElementById('descricao').value = '';
        document.getElementById('categoria').value = '';
    }

    function editarProduto(produto) {
        document.getElementById('produto_id').value = produto.id;
        document.getElementById('nome').value = produto.nome;
        document.getElementById('descricao').value = produto.descricao;
        document.getElementById('categoria').value = produto.categoria_id;
    }

    function fecharModais() {
        $('#stockModal').modal('hide');
        $('#infoStockModal').modal('hide');
    }

    function verificarStock(produtoId, nomeProduto) {

        fetch(`/stock/check/${produtoId}`)
            .then(res => res.json())
            .then(data => {

                if (data.existe) {

                    document.getElementById('stockMensagem').innerText =
                        `O produto "${nomeProduto}" já possui stock registado.`;

                    $('#infoStockModal').modal('show');

                } else {

                    document.getElementById('stock_produto_id').value = produtoId;
                    document.getElementById('stock_produto_nome').innerText = nomeProduto;

                    $('#stockModal').modal('show');
                }

            })
            .catch(() => {
                alert("Erro ao verificar stock.");
        });
    }

    function abrirStock(produto) {
        document.getElementById('stock_produto_id').value = produto.id;
        document.getElementById('stock_produto_nome').innerText = produto.nome;
    }

</script>

@endsection