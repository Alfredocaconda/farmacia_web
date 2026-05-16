@extends('layouts.app')
@section('title', 'STOCK')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div class="header-title" style="display: flex; justify-content: space-between; width: 100%">
                    <h4 class="card-title">Cadastrar STOCK</h4>
                    <a href="#Cadastrar" data-toggle="modal" style="font-size: 20pt"><i class="fa fa-plus-circle"></i></a>
                </div>
            </div>
            @if(session('ERRO'))
                    <div class="alert alert-danger">
                        <p>{{session('ERRO')}}</p>
                    </div>
                @endif
                @if(session('SUCESSO'))
                    <div class="alert alert-success">
                        <p>{{session('SUCESSO')}}</p>
                    </div>
                @endif
                @if($totalBaixoStock > 0)
                <ul class="list-group">
                    @foreach($baixoStock as $item)
                        <li class="list-group-item d-flex justify-content-between">
                            {{ $item->produto->nome }}
                            <span class="badge bg-danger">{{ $item->quantidade }}</span>
                        </li>
                    @endforeach
                </ul>
                @endif

            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table data-tables table-striped">
                    <thead>
                        <tr class="ligth">
                            <th>Produto</th>
                            <th>Preço </th>
                            <th>QTD</th>
                            <th>Data Entrada</th>
                            <th>Codigo Barra</th>
                            <th>Fornecedor</th>
                            <th>Caducidade</th>
                            <th>Funcionario</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($stock as $dados)
                            <tr class="
                                @if($dados->alerta == 'expirado') table-danger
                                @elseif($dados->alerta == 'critico') table-warning
                                @elseif($dados->alerta == 'atencao') table-info
                                @endif
                            ">
                            <td>{{ $dados->produto->nome." / ".$dados->produto->descricao." / ".$dados->produto->categoria }}</td>
                            <td>
                                <span class="badge 
                                    {{ $dados->status_validade == 'expirado' ? 'bg-danger' : '' }}
                                    {{ $dados->status_validade == 'critico' ? 'bg-warning' : '' }}
                                    {{ $dados->status_validade == 'atencao' ? 'bg-info' : '' }}
                                ">
                                    {{ strtoupper($dados->status_validade) }}
                                </span>
                            </td>
                            <td>{{ $dados->preco." KZ" }}</td>
                            <td>{{ $dados->qtd_stock }}</td>
                            <td>{{ $dados->data_entrada }}</td>
                            <td>{{ $dados->codigo_barra }}</td>
                            <td>{{ $dados->fornecedor }}</td>
                            <td>{{ $dados->caducidade }}</td>
                            <td>{{ $dados->funcionario->nome }}</td>
                            <td>
                                <a href="#AumentarStock" data-toggle="modal" class="text-success" onclick="abrirModalAumentar({{ $dados->id }}, '{{ $dados->produto->nome }}')">
                                    <i class="fa fa-plus-circle"></i>
                                </a>
                                <a href="#Cadastrar" data-toggle="modal" class="text-primary" onclick='editar(@json($dados))'><i class="fa fa-edit"></i></a>
                                <a href="{{ route('stock.destroy', $dados->id) }}" class="text-danger"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>
    </div>
    
</div>
<!-- Modal -->
<div class="modal fade" id="Cadastrar" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
                <div class="modal-header">
                        <h5 class="modal-title">DAR ENTRADA NO STOCK</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>
            <div class="modal-body">
                <div class="container-fluid">
                   <form action="{{route('stock.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                         <input type="hidden" name="id_funcionario" value="{{Auth::guard('funcionario')->user()->id}}">
                         <input type="hidden" name="id_produto" id="id_produto" value="{{ $valor->id ?? '' }}">
                         <input type="hidden" name="id" id="id">

                       @if($valor)
                            <p><strong>Nome do Produto : </strong>{{ $valor->nome }}</p>
                        @else
                        <p><strong>Produto Selecionado: </strong><span id="nomeProdutoSelecionado">Selecione um produto</span></p>
                        @endif
                        <div class="row">
                            <x-input-normal id="preco" name="preco" type="number" titulo="Preço" alert="" />
                            <x-input-normal id="qtd_stock" name="qtd_stock" type="number" titulo="Quantidade" alert="" />
                            <x-input-normal id="fornecedor" name="fornecedor" type="text" titulo="Fornecedor" alert="" />
                            <x-input-normal id="codigo_barra" name="codigo_barra" type="text" titulo="Codigo de Barra" alert="" />
                            <x-input-normal id="caducidade" name="caducidade" type="date" titulo="Caducidade" alert="" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <x-botao-form />
            </form>
            </div>
        </div>
    </div>
</div>
 <!-- Modal para Aumentar Quantidade -->
<div class="modal fade" id="AumentarStock" tabindex="-1" role="dialog" aria-labelledby="modalAumentarTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('stock.store') }}" method="POST">
                <input type="hidden" name="tipo" value="aumentar">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Aumentar Quantidade no Stock</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Produto:</strong> <span id="nomeProdutoModal"></span></p>
                    <input type="hidden" name="id" id="stock_id_aumentar">
                    <div class="form-group">
                        <label for="qtd_stock_nova">Quantidade a Adicionar</label>
                        <input type="number" name="qtd_stock" id="qtd_stock_nova" class="form-control" required min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <x-botao-form />
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function editar(valor) {
        document.getElementById('id').value = valor.id;
        document.getElementById('preco').value = valor.preco;
        document.getElementById('preco').value = valor.preco;
        document.getElementById('fornecedor').value = valor.fornecedor;
        document.getElementById('codigo_barra').value = valor.codigo_barra;
        document.getElementById('qtd_stock').value = valor.qtd_stock;
        document.getElementById('caducidade').value = valor.caducidade;
        document.getElementById('id_produto').value = valor.id_produto;


        if (valor.produto) {
            document.getElementById('nomeProdutoSelecionado').textContent = valor.produto.nome + " / " + valor.produto.descricao + " / " + valor.produto.categoria;
            document.getElementById('id_produto').value = valor.produto.id;
        }
    }
    function limpar() {
        document.getElementById('id').value = "";
        document.getElementById('qtd_stock').value = "";
        document.getElementById('caducidade').value = "";
        document.getElementById('fornecedor').value = "";
        document.getElementById('codigo_barra').value = "";
        document.getElementById('preco').value = "";
        document.getElementById('id_produto').value = "";
    }
     function abrirModalAumentar(stockId, nomeProduto) {
        document.getElementById('stock_id_aumentar').value = stockId;
        document.getElementById('nomeProdutoModal').textContent = nomeProduto;
        document.getElementById('qtd_stock_nova').value = '';
    }
</script>
@endsection
