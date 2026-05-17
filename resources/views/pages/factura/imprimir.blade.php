<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recibo - {{ $vendas[0]->codigo_fatura }}</title>
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
<body >

    <div class="center">
        <h2>FARMÁCIA 4 DE SETEMBRO</h2>
         <p>BENGUELA-ANGOLA</p>
        <p>BAIRRO 4 DE ABRIL</p>
        <p>Telefone: 940724510/998724510</p>
        <p>Email: farmacia5desetembro@gmail.com</p>
        <p>NIF: 003297956BA039</p>
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
