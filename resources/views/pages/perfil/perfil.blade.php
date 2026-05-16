@extends('layouts.app')

@section('content')
<div class="media-body">
    <h2 class="mb-2">Meu Perfil</h2>
    <h4 class="mb-2">Nome Completo : {{Auth::guard('funcionario')->user()->nome}}</h4>
    <h4 class="mb-2">E-mail : {{Auth::guard('funcionario')->user()->email}}</h4>
    <h4 class="mb-2">Função : {{Auth::guard('funcionario')->user()->funcao}}</h4>
    <h4 class="mb-2">Data do Contrato : {{Auth::guard('funcionario')->user()->data_contrato}}</h4>
</div>
@endsection
