@extends('layouts.app')
@section('title', 'Empresa')
@section('content')

<div class="container-fluid">

    {{-- HEADER ERP --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Padrão Empresa</h4>

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
                        <th>Empresa</th>
                        <th>Nif</th>
                        <th>Provincia-Municipio</th>
                        <th>Bairro</th>
                        <th>Contacto</th>
                        <th>Email</th>
                        <th width="180">Ações</th>
                    </tr>
                </thead>

                <tbody>
                   @if($empresa)
                    <tr>
                        <td>{{ $empresa->nome ?? '' }}</td>
                        <td>{{ $empresa->nif ?? '' }}</td>
                        <td>{{ $empresa->endereco ?? '' }}</td>
                        <td>{{ $empresa->bairro ?? '' }}</td>
                        <td>{{ $empresa->telefone ?? '' }}</td>
                        <td>{{ $empresa->email ?? '' }}</td>
                        <td>
                            <button class="btn btn-sm btn-info"
                                data-toggle="modal"
                                data-target="#empresaModal"
                                onclick='editarEmpresa(@json($empresa))'>
                                Editar
                            </button>

                            <form action="{{ route('empresa.deletar') }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('Eliminar dados da empresa?')">
                                    Apagar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @else
                    <tr>
                        <td colspan="7" class="text-center">
                            Nenhum dado da empresa cadastrado
                        </td>
                    </tr>
                    @endif
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

            <form action="{{ route('empresa.salvar') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <input type="text" name="nome" placeholder="Nome" value="{{ $empresa->nome ?? '' }}">
                <input type="text" name="endereco" placeholder="Endereço" value="{{ $empresa->endereco ?? '' }}">
                <input type="text" name="bairro" placeholder="Bairro" value="{{ $empresa->bairro ?? '' }}">
                <input type="text" name="telefone" placeholder="Telefone" value="{{ $empresa->telefone ?? '' }}">
                <input type="email" name="email" placeholder="Email" value="{{ $empresa->email ?? '' }}">
                <input type="text" name="nif" placeholder="NIF" value="{{ $empresa->nif ?? '' }}">

                <input type="file" name="logo">

                <button type="submit">Salvar</button>
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