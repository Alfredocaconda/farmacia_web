@extends('layouts.app')
@section('title', 'DASHBOARD')
@section('content')

@php
use Illuminate\Support\Facades\DB;

/* ===================== GRÁFICOS ===================== */

// Vendas por mês
$monthlySales = \App\Models\Venda::select(
    DB::raw('MONTH(created_at) as mes'),
    DB::raw('SUM(quantidade) as total')
)
->groupBy('mes')
->orderBy('mes')
->get();

// Vendas diárias
$dailySales = \App\Models\Venda::select(
    DB::raw('DATE(created_at) as data'),
    DB::raw('SUM(quantidade) as total')
)
->groupBy('data')
->orderBy('data')
->get();

// Top produtos
$topProducts = \App\Models\Venda::with('stock.produto')
    ->select('id', DB::raw('SUM(quantidade) as subtotal'))
    ->groupBy('id')
    ->get()
    ->map(function ($v) {
        return [
            'nome' => $v->stock->produto->nome,
            'subtotal' => $v->subtotal
        ];
    });
    
// Receita (ajusta se tiver campo subtotal)
$revenue = \App\Models\Venda::select(
    DB::raw('DATE(created_at) as data'),
    DB::raw('SUM(subtotal) as subtotal')
)
->groupBy('data')
->orderBy('data')
->get();
@endphp

<div class="container-fluid">

    {{-- ================= CARDS ERP ================= --}}
    <div class="row mb-4">

        <div class="col-lg-3 col-md-6">
            <div class="card p-3 text-center shadow-sm">
                <p>Funcionários</p>
                <h3>{{ \App\Models\Funcionario::count() }}</h3>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card p-3 text-center shadow-sm">
                <p>Stock Total</p>
                <h3>{{ \App\Models\Stock::sum('qtd_stock') }}</h3>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card p-3 text-center shadow-sm">
                <p>Vendas</p>
                <h3>{{ \App\Models\Venda::sum('quantidade') }}</h3>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card p-3 text-center shadow-sm">
                <p>Caducados</p>
                <h3>{{ \App\Models\Stock::whereDate('caducidade','<',now())->count() }}</h3>
            </div>
        </div>

    </div>

    {{-- ================= GRÁFICOS ================= --}}
    <div class="row">

        <div class="col-lg-6 mb-4">
            <div class="card p-3">
                <h6>📊 Vendas por Mês</h6>
                <div id="chartMonth"></div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card p-3">
                <h6>📉 Vendas Diárias</h6>
                <div id="chartDaily"></div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card p-3">
                <h6>🏆 Top Produtos</h6>
                <div id="chartTop"></div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card p-3">
                <h6>💰 Receita</h6>
                <div id="chartRevenue"></div>
            </div>
        </div>

    </div>
<h5>Expirados: {{ $expirados }}</h5>
<h5>Críticos: {{ $criticos }}</h5>
<h4>Lucro Total: {{ number_format($lucro, 2) }} Kz</h4>
@if($totalStockBaixo > 0)
<div class="alert alert-danger">
    ⚠️ Existem {{ $totalStockBaixo }} produtos com stock baixo!
</div>
@endif
</div>

{{-- ================= APEXCHARTS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
/* ===== VENDAS POR MÊS ===== */
new ApexCharts(document.querySelector("#chartMonth"), {
    chart: { type: 'bar', height: 300 },
    series: [{
        name: 'Vendas',
        data: {!! json_encode($monthlySales->pluck('total')) !!}
    }],
    xaxis: {
        categories: {!! json_encode($monthlySales->pluck('mes')) !!}
    }
}).render();

/* ===== DIÁRIO ===== */
new ApexCharts(document.querySelector("#chartDaily"), {
    chart: { type: 'line', height: 300 },
    series: [{
        name: 'Vendas',
        data: {!! json_encode($dailySales->pluck('total')) !!}
    }],
    xaxis: {
        categories: {!! json_encode($dailySales->pluck('data')) !!}
    }
}).render();

/* ===== TOP PRODUTOS ===== */
new ApexCharts(document.querySelector("#chartTop"), {
    chart: { type: 'donut', height: 300 },
    series: {!! json_encode($topProducts->pluck('total')) !!},
    labels: {!! json_encode($topProducts->pluck('nome')) !!}
}).render();

/* ===== RECEITA ===== */
new ApexCharts(document.querySelector("#chartRevenue"), {
    chart: { type: 'area', height: 300 },
    series: [{
        name: 'Receita',
        data: {!! json_encode($revenue->pluck('total')) !!}
    }],
    xaxis: {
        categories: {!! json_encode($revenue->pluck('data')) !!}
    }
}).render();
</script>

@endsection