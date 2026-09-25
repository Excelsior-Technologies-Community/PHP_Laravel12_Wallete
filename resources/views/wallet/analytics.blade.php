@extends('layouts.wallet')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">📈 Financial Analytics & Cash Flow Intelligence</h1>
    <p class="text-gray-500 mt-1">Visualize cash inflow, spending velocity, and transaction trends.</p>
</div>

<!-- Financial Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="text-xs font-semibold text-gray-400 uppercase">Current Balance</div>
        <div class="text-3xl font-bold text-gray-800 mt-1">${{ number_format($analytics['balance'], 2) }}</div>
    </div>
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="text-xs font-semibold text-green-600 uppercase">Total Cash-In (Deposits)</div>
        <div class="text-3xl font-bold text-green-600 mt-1">+${{ number_format($analytics['cash_in'], 2) }}</div>
    </div>
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="text-xs font-semibold text-red-600 uppercase">Total Cash-Out (Withdrawals & P2P)</div>
        <div class="text-3xl font-bold text-red-600 mt-1">-${{ number_format($analytics['cash_out'], 2) }}</div>
    </div>
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="text-xs font-semibold text-indigo-600 uppercase">P2P Transfers Volume</div>
        <div class="text-3xl font-bold text-indigo-600 mt-1">${{ number_format($analytics['total_transfers'], 2) }}</div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-4">🍩 Cash-In vs Cash-Out Breakdown</h3>
        <div style="height: 260px;">
            <canvas id="cashFlowPieChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-4">📈 7-Day Cash Flow Timeline</h3>
        <div style="height: 260px;">
            <canvas id="cashFlowLineChart"></canvas>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Doughnut Chart
        const pieCtx = document.getElementById('cashFlowPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cash In (Deposits)', 'Cash Out (Withdrawals & Transfers)'],
                datasets: [{
                    data: [{{ $analytics['cash_in'] }}, {{ $analytics['cash_out'] }}],
                    backgroundColor: ['#10b981', '#ef4444'],
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Line Chart
        const lineCtx = document.getElementById('cashFlowLineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($analytics['trend_labels']) !!},
                datasets: [
                    {
                        label: 'Cash-In ($)',
                        data: {!! json_encode($analytics['cash_in_trend']) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Cash-Out ($)',
                        data: {!! json_encode($analytics['cash_out_trend']) !!},
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    });
</script>

@endsection
