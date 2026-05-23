<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recibo - {{ $vendas[0]->codigo_fatura }}</title>
    @php
        $empresa = null;
        $path = storage_path('app/empresa.txt');

        if (file_exists($path)) {
            $empresa = json_decode(file_get_contents($path));
        }
    @endphp

    <img src="{{ asset($empresa->logo ?? 'images/logo.png') }}"
        class="img-fluid rounded-normal light-logo">
    <style>
        @media print {
            body {
                width: 210mm;
                height: 297mm;
                margin: 0;
                padding: 20mm;
                font-family: Arial, sans-serif;
                font-size: 12pt;
                overflow: hidden;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            width: 800px;
            margin: auto;
            padding: 20px;
        }

        .center { text-align: center; }
        .border-top { border-top: 1px solid #000; margin-top: 20px; padding-top: 10px; }
        .border-bottom { border-bottom: 1px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #ccc; padding: 8px; text-align: left; }
        .totais td { font-weight: bold; }
        .right { text-align: right; }
        
    </style>
</head>
@php
    $empresa = null;
    $path = storage_path('app/empresa.txt');

    if (file_exists($path)) {
        $empresa = json_decode(file_get_contents($path));
    }
@endphp
<body >

    <div class="center">
        @if($empresa && $empresa->logo)
            <img src="{{ asset($empresa->logo) }}" width="100">
        @endif

        <h2>{{ $empresa->nome ?? 'Empresa' }}</h2>
        <p>{{ $empresa->endereco ?? '' }}</p>
        <p>{{ $empresa->bairro ?? '' }}</p>
        <p>Telefone: {{ $empresa->telefone ?? '' }}</p>
        <p>Email: {{ $empresa->email ?? '' }}</p>
        <p>NIF: {{ $empresa->nif ?? '' }}</p>
    </div>

    <div class="border-top border-bottom">
        <p><strong>RECIBO DE VENDA</strong></p>
        <p><strong>Fatura Nº:</strong> {{ $vendas[0]->codigo_fatura }}</p>
        <p><strong>Nome do Cliente:</strong> {{ $vendas[0]->cliente }}</p>
        <p><strong>Data:</strong> {{ \Carbon\Carbon::parse($vendas[0]->data_venda)->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Qtd</th>
                <th>Preço Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($vendas as $venda)
                <tr>
                    <td>{{ $venda->produto->nome ?? 'Removido' }}</td>
                    <td>{{ $venda->quantidade }}</td>
                    <td>{{ number_format($venda->preco_unitario, 2, ',', '.') }}</td>
                    <td>{{ number_format($venda->subtotal, 2, ',', '.') }}</td>
                </tr>
                @php $total += $venda->subtotal; @endphp
            @endforeach
        </tbody>
    </table>

    <table class="totais" style="margin-top: 30px;">
        <tr>
            <td>Total:</td>
            <td class="right">{{ number_format($total, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Valor Entregue:</td>
            <td class="right">{{ number_format($valor_entregue, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Troco:</td>
            <td class="right">{{ number_format($troco, 2, ',', '.') }}</td>
        </tr>
    </table>
    <div class="border-top border-bottom">
        <p><strong>Funcionário:</strong> {{ $funcionario->nome ?? 'Desconhecido' }}</p>
    </div>
    <div class="center border-top">
        <p>Obrigado pela preferência!</p>
        <p>Desejamos melhoria!</p>
    </div>
<script>
        window.onload = function() {
            window.print();
            setTimeout(function () {
                window.location.href = "{{ route('vendas.index') }}";
            }, 1000);
        };
    </script>
</body>
</html>
