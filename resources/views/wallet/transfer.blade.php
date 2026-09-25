@extends('layouts.wallet')

@section('content')

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">💸 Peer-to-Peer (P2P) Wallet Transfer</h1>
    <p class="text-gray-500 mt-1">Transfer funds instantly to any registered user email address.</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg">
        ✅ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg">
        ❌ {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Balance & Security Info Card -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl p-6 shadow-md">
        <h3 class="text-sm font-semibold opacity-80 uppercase tracking-wider">Available Wallet Balance</h3>
        <div class="text-4xl font-extrabold mt-2">${{ number_format($user->balance, 2) }}</div>
        
        <div class="mt-6 pt-4 border-t border-white/20 text-xs space-y-1">
            <p>🔒 <b>Daily Transfer Limit:</b> $5,000.00 / day</p>
            <p>⚠️ <b>High Value Warning:</b> Single transfer &ge; $1,000.00</p>
            <p>⚡ <b>Fee:</b> $0.00 (Instant P2P Transfer)</p>
        </div>
    </div>

    <!-- P2P Transfer Form -->
    <div class="lg:col-span-2 bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Send Money to Recipient</h3>
        
        <form action="{{ route('wallet.transfer.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Email Address:</label>
                <input type="email" name="email" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="friend@example.com" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Transfer Amount ($):</label>
                <input type="number" step="0.01" min="0.01" max="100000" name="amount" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="50.00" required>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                💸 Confirm & Send Transfer
            </button>
        </form>
    </div>
</div>

<!-- Recent Transfers Table -->
<div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
    <h3 class="text-lg font-bold text-gray-800 mb-4">📜 Recent P2P Transfer Activity</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b bg-gray-50 text-gray-600 text-sm">
                    <th class="py-3 px-4">Transaction ID</th>
                    <th class="py-3 px-4">Type</th>
                    <th class="py-3 px-4">Amount</th>
                    <th class="py-3 px-4">Date & Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($recentTransfers as $tx)
                    <tr>
                        <td class="py-3 px-4 font-mono">#{{ $tx->id }}</td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                P2P Transfer
                            </span>
                        </td>
                        <td class="py-3 px-4 font-bold text-purple-600">
                            -${{ number_format(abs((float)$tx->amount), 2) }}
                        </td>
                        <td class="py-3 px-4 text-gray-500">{{ $tx->created_at ? $tx->created_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-500">No recent P2P transfers recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
