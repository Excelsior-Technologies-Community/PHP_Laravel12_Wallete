@extends('layouts.wallet')

@section('content')

    <h2 class="text-2xl font-bold mb-6 text-center">
        My Wallet
    </h2>

    <!-- Balance -->
    <div class="mb-6 text-center">
        <h3 class="text-xl font-semibold">
            Current Balance:
            <span class="text-blue-600">
                ₹ {{ $balance }}
            </span>
        </h3>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- Error Message -->
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Deposit & Withdraw Forms -->
    <div class="grid grid-cols-2 gap-6 mb-8">

        <!-- Deposit -->
        <form method="POST" action="{{ route('wallet.deposit') }}" class="bg-gray-50 p-4 rounded shadow">
            @csrf
            <h4 class="font-semibold mb-3 text-green-600">Add Money</h4>

            <input type="number"
                   name="amount"
                   placeholder="Enter amount"
                   class="w-full border p-2 rounded mb-3">

            <button type="submit"
                    class="w-full bg-green-500 text-white py-2 rounded hover:bg-green-600">
                Deposit
            </button>
        </form>

        <!-- Withdraw -->
        <form method="POST" action="{{ route('wallet.withdraw') }}" class="bg-gray-50 p-4 rounded shadow">
            @csrf
            <h4 class="font-semibold mb-3 text-red-600">Withdraw Money</h4>

            <input type="number"
                   name="amount"
                   placeholder="Enter amount"
                   class="w-full border p-2 rounded mb-3">

            <button type="submit"
                    class="w-full bg-red-500 text-white py-2 rounded hover:bg-red-600">
                Withdraw
            </button>
        </form>

    </div>

    <!-- Transaction History -->
    <h4 class="text-lg font-semibold mb-3">Transaction History</h4>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border">Type</th>
                    <th class="p-2 border">Amount</th>
                    <th class="p-2 border">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td class="p-2 border text-center">
                            {{ ucfirst($transaction->type) }}
                        </td>

                        <td class="p-2 border text-center">
                            @if($transaction->type == 'deposit')
                                <span class="text-green-600 font-semibold">
                                    + ₹ {{ abs($transaction->amount) }}
                                </span>
                            @else
                                <span class="text-red-600 font-semibold">
                                    - ₹ {{ abs($transaction->amount) }}
                                </span>
                            @endif
                        </td>

                        <td class="p-2 border text-center">
                            {{ $transaction->created_at->format('d M Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center p-4">
                            No Transactions Yet
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
