@extends('layouts.app')
@section('title', 'FUNCIONÁRIO')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div class="header-title" style="display: flex; justify-content: space-between; width: 100%">
                    <h4 class="card-title">Funcionarios</h4>
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
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table data-tables table-striped">
                    <thead>
                        <tr class="ligth">
                            <th>NOME COMPLETO</th>
                            <th>Nº BILHETE</th>
                            <th>FUNÇÃO</th>
                            <th>TELEFONE</th>
                            <th>DATA CONTRATO</th>
                            <th>ENDEREÇO</th>
                            <th>E-mail</th>
                            <th>SENHA</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($funcionario as $func)
                            <tr>
                                <td>{{$func->nome}}</td>
                                <td>{{$func->n_bilhete}}</td>
                                <td>{{$func->funcao}}</td>
                                <td>{{$func->telefone}}</td>
                                <td>{{$func->data_contrato}}</td>
                                <td>{{$func->endereco}}</td>
                                <td>{{$func->email}}</td>
                                <td>{{$func->senha}}</td>
                                <td>
                                    <a href="#Cadastrar" data-toggle="modal" class="text-primary" onclick="editar({{ json_encode($func) }})"><i class="fa fa-edit"></i></a>
                                    <a href="{{route('funcionario.destroy',$func->id)}}" class="text-danger"><i class="fa fa-trash"></i></a>
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
                <h5 class="modal-title">Cadastrar Funcionários</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="container-fluid">
                    
                    <!-- EXIBIR ERROS DE VALIDAÇÃO AQUI -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('funcionario.store') }}" method="post">
                        @csrf
                        <input type="hidden" name="id" id="id">
                        <div class="row">
                            <x-input-normal id="nome" name="nome" type="text" titulo="Nome Completo" alert="" />
                            <x-input-normal id="endereco" name="endereco" type="text" titulo="Endereço" alert="" />
                            <x-input-normal id="email" name="email" type="email" titulo="E-mail" alert="" />
                            <div class="form-group col-12 col-md-6 col-lg-6">
                                <label for="telefone">Nº do Telefone <span style="color: red;">*</span></label>
                                <div class="form-input">
                                    <input type="text" 
                                           class="form-control" 
                                           name="telefone" 
                                           id="telefone" 
                                           maxlength="9" 
                                           oninput="formatTelefone(this)" 
                                           placeholder="9XX-XXX-XXX">
                                    
                                    <!-- Mostra quantos caracteres ainda faltam -->
                                    <small id="char_count_telefone" class="form-text text-muted">Faltam 9 caracteres</small>
                                </div>
                            </div>
                            <div class="form-group col-12 col-md-6 col-lg-6">
                                <label for="n_bilhete">Número do BI <span style="color: red;">*</span></label>
                                <div class="form-input">
                                    <input type="text" 
                                           class="form-control" 
                                           name="n_bilhete" 
                                           id="n_bilhete" 
                                           maxlength="14" 
                                           oninput="formatBI(this)" 
                                           placeholder="123456789AB123">
                                    
                                    <!-- Mostra quantos caracteres ainda faltam -->
                                    <small id="char_count" class="form-text text-muted">Faltam 14 caracteres</small>
                                </div>
                            </div>                        
                            <x-select name="funcao">
                                
                            </x-select>    
                            <x-input-normal id="data_contrato" name="data_contrato" type="date" titulo="Data de Contrato" alert="" />
                            <x-input-normal id="senha" name="senha" type="password" titulo="Senha" alert="" />
                        </div>

                        <div class="modal-footer">
                            <x-botao-form />
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
<script>

    function editar(valor) {
        if (!valor) {
            console.error("Erro: Dados do funcionário não encontrados.");
            return;
        }

        document.getElementById('id').value = valor.id || '';
        document.getElementById('nome').value = valor.nome || '';
        document.getElementById('funcao').value = valor.funcao || '';
        document.getElementById('email').value = valor.email || '';
        document.getElementById('n_bilhete').value = valor.n_bilhete || '';
        document.getElementById('telefone').value = valor.telefone || '';
        document.getElementById('endereco').value = valor.endereco || '';
        document.getElementById('senha').value = valor.senha || '';
        document.getElementById('data_contrato').value = valor.data_contrato || '';

        // Modificar a URL do formulário para apontar para update se for edição
        let form = document.getElementById('formFuncionario');
        if (valor.id) {
            form.action = `/funcionario/${valor.id}`;  // Ajuste conforme sua rota de atualização
            form.method = "POST"; // Laravel aceita PUT/PATCH com _method
            form.innerHTML += '<input type="hidden" name="_method" value="PUT">';
        } else {
            form.action = "{{ route('funcionario.store') }}"; // Criar novo
        }
    }

    function limpar() {
        document.getElementById('id').value = "";
        document.getElementById('nome').value = "";
        document.getElementById('funcao').value = "";
        document.getElementById('telefone').value = "";
        document.getElementById('email').value = "";
        document.getElementById('n_bilhete').value = "";
        document.getElementById('endereco').value = "";
        document.getElementById('senha').value = "";
        document.getElementById('data_contrato').value = "";
    }

    function formatBI(input) {
        let value = input.value.toUpperCase(); // Converte letras para maiúsculas
        let formattedValue = "";
        
        for (let i = 0; i < value.length; i++) {
            if (i < 9) { 
                // Primeiros 9 caracteres devem ser números
                if (/[0-9]/.test(value[i])) {
                    formattedValue += value[i];
                }
            } else if (i < 11) { 
                // Os próximos 2 caracteres devem ser letras
                if (/[A-Z]/.test(value[i])) {
                    formattedValue += value[i];
                }
            } else { 
                // Os últimos 3 caracteres devem ser números
                if (/[0-9]/.test(value[i])) {
                    formattedValue += value[i];
                }
            }
        }

        // Atualiza o valor do input com a formatação correta
        input.value = formattedValue;

        // Atualiza a contagem de caracteres restantes
        let maxLength = 14;
        let currentLength = input.value.length;
        let remaining = maxLength - currentLength;

        let counterElement = document.getElementById("char_count");
        counterElement.textContent = remaining > 0 ? `Faltam ${remaining} caracteres` : "Formato completo!";
    }
    function formatTelefone(input) {
        let value = input.value.toUpperCase(); // Converte letras para maiúsculas
        let formattedValue = "";
        
        for (let i = 0; i < value.length; i++) {
            if (i < 9) { 
                // Primeiros 9 caracteres devem ser números
                if (/[0-9]/.test(value[i])) {
                    formattedValue += value[i];
                }
            } else if (i < 11) { 
                // Os próximos 2 caracteres devem ser letras
                if (/[A-Z]/.test(value[i])) {
                    formattedValue += value[i];
                }
            } else { 
                // Os últimos 3 caracteres devem ser números
                if (/[0-9]/.test(value[i])) {
                    formattedValue += value[i];
                }
            }
        }

        // Atualiza o valor do input com a formatação correta
        input.value = formattedValue;

        // Atualiza a contagem de caracteres restantes
        let maxLength = 9;
        let currentLength = input.value.length;
        let remaining = maxLength - currentLength;
        let counterElement = document.getElementById("char_count_telefone");
        counterElement.textContent = remaining > 0 ? `Faltam ${remaining} caracteres` : "Formato completo!";
    }
</script>
@endsection
