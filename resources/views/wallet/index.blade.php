@extends('layouts.wallet')

@section('content')

    <!-- ========================================================= -->
    <!-- PAGE HEADER -->
    <!-- ========================================================= -->

    <div class="mb-8">

        <h1 class="text-3xl font-bold text-gray-800">
            My Wallet
        </h1>

        <p class="text-gray-500 mt-1">
            Manage your balance, transactions and wallet insights
        </p>

    </div>


    <!-- ========================================================= -->
    <!-- SUCCESS MESSAGE -->
    <!-- ========================================================= -->

    @if(session('success'))

        <div class="mb-6 bg-green-100 border border-green-300
                    text-green-700 px-4 py-3 rounded-lg">

            {{ session('success') }}

        </div>

    @endif


    <!-- ========================================================= -->
    <!-- ERROR MESSAGE -->
    <!-- ========================================================= -->

    @if(session('error'))

        <div class="mb-6 bg-red-100 border border-red-300
                    text-red-700 px-4 py-3 rounded-lg">

            {{ session('error') }}

        </div>

    @endif


    <!-- ========================================================= -->
    <!-- VALIDATION ERRORS -->
    <!-- ========================================================= -->

    @if($errors->any())

        <div class="mb-6 bg-red-100 border border-red-300
                    text-red-700 px-4 py-3 rounded-lg">

            <ul class="list-disc list-inside">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    <!-- ========================================================= -->
    <!-- WALLET ANALYTICS -->
    <!-- ========================================================= -->

    <div class="grid grid-cols-1 md:grid-cols-2
                lg:grid-cols-4 gap-6 mb-8">

        <!-- Balance -->

        <div class="bg-white rounded-xl shadow-sm
                    border border-gray-200 p-6">

            <p class="text-sm font-medium text-gray-500">
                Current Balance
            </p>

            <h2 class="text-3xl font-bold
                       text-blue-600 mt-2">

                ₹ {{ number_format($balance, 2) }}

            </h2>

            <p class="text-xs text-gray-400 mt-2">
                Available wallet balance
            </p>

        </div>


        <!-- Deposits -->

        <div class="bg-white rounded-xl shadow-sm
                    border border-gray-200 p-6">

            <p class="text-sm font-medium text-gray-500">
                Total Deposits
            </p>

            <h2 class="text-3xl font-bold
                       text-green-600 mt-2">

                ₹ {{ number_format($totalDeposits, 2) }}

            </h2>

            <p class="text-xs text-gray-400 mt-2">

                {{ $depositCount }}
                deposit transactions

            </p>

        </div>


        <!-- Withdrawals -->

        <div class="bg-white rounded-xl shadow-sm
                    border border-gray-200 p-6">

            <p class="text-sm font-medium text-gray-500">
                Total Withdrawals
            </p>

            <h2 class="text-3xl font-bold
                       text-red-600 mt-2">

                ₹ {{ number_format($totalWithdrawals, 2) }}

            </h2>

            <p class="text-xs text-gray-400 mt-2">

                {{ $withdrawalCount }}
                withdrawal transactions

            </p>

        </div>


        <!-- Transactions -->

        <div class="bg-white rounded-xl shadow-sm
                    border border-gray-200 p-6">

            <p class="text-sm font-medium text-gray-500">
                Total Transactions
            </p>

            <h2 class="text-3xl font-bold
                       text-purple-600 mt-2">

                {{ $transactionCount }}

            </h2>

            <p class="text-xs text-gray-400 mt-2">
                All wallet transactions
            </p>

        </div>

    </div>


    <!-- ========================================================= -->
    <!-- WALLET ALERTS -->
    <!-- ========================================================= -->

    @if($isLowBalance)

        <div class="mb-6 bg-yellow-50 border
                    border-yellow-300 rounded-xl p-5">

            <div class="flex items-start gap-4">

                <div class="text-2xl">
                    ⚠️
                </div>

                <div>

                    <h3 class="font-bold text-yellow-800">

                        Low Balance Warning

                    </h3>

                    <p class="text-sm text-yellow-700 mt-1">

                        Your current wallet balance is

                        <strong>
                            ₹ {{ number_format($balance, 2) }}
                        </strong>.

                        Your low-balance threshold is

                        <strong>
                            ₹ {{ number_format($lowBalanceThreshold, 2) }}
                        </strong>.

                        Consider adding money to your wallet.

                    </p>

                </div>

            </div>

        </div>

    @endif


    @if($largeWithdrawal)

        <div class="mb-6 bg-orange-50 border
                    border-orange-300 rounded-xl p-5">

            <div class="flex items-start gap-4">

                <div class="text-2xl">
                    🚨
                </div>

                <div>

                    <h3 class="font-bold text-orange-800">

                        Large Withdrawal Alert

                    </h3>

                    <p class="text-sm text-orange-700 mt-1">

                        A withdrawal of

                        <strong>
                            ₹ {{ number_format($largeWithdrawalAmount = abs((float) $largeWithdrawal->amount), 2) }}
                        </strong>

                        or more was detected.

                        Transaction ID:

                        <strong>
                            #{{ $largeWithdrawal->id }}
                        </strong>

                    </p>

                </div>

            </div>

        </div>

    @endif


    <!-- ========================================================= -->
    <!-- WALLET ACTIVITY INSIGHTS -->
    <!-- ========================================================= -->

    <div class="bg-white rounded-xl shadow-sm
                border border-gray-200 p-6 mb-8">

        <div class="flex items-center gap-3 mb-6">

            <div class="text-2xl">
                💡
            </div>

            <div>

                <h2 class="text-xl font-bold text-gray-800">

                    Wallet Activity Insights

                </h2>

                <p class="text-sm text-gray-500">

                    Automatic analysis of your wallet activity

                </p>

            </div>

        </div>


        <div class="grid grid-cols-1 md:grid-cols-2
                    lg:grid-cols-4 gap-5">

            <!-- Highest Deposit -->

            <div class="bg-green-50 border
                        border-green-200 rounded-lg p-5">

                <p class="text-sm font-medium
                          text-green-700">

                    Highest Deposit

                </p>

                <p class="text-2xl font-bold
                          text-green-700 mt-2">

                    ₹ {{ number_format($highestDepositAmount, 2) }}

                </p>

                @if($highestDeposit)

                    <p class="text-xs text-green-600 mt-2">

                        Transaction #{{ $highestDeposit->id }}

                    </p>

                @else

                    <p class="text-xs text-gray-500 mt-2">

                        No deposits yet

                    </p>

                @endif

            </div>


            <!-- Highest Withdrawal -->

            <div class="bg-red-50 border
                        border-red-200 rounded-lg p-5">

                <p class="text-sm font-medium
                          text-red-700">

                    Highest Withdrawal

                </p>

                <p class="text-2xl font-bold
                          text-red-700 mt-2">

                    ₹ {{ number_format($highestWithdrawalAmount, 2) }}

                </p>

                @if($highestWithdrawal)

                    <p class="text-xs text-red-600 mt-2">

                        Transaction #{{ $highestWithdrawal->id }}

                    </p>

                @else

                    <p class="text-xs text-gray-500 mt-2">

                        No withdrawals yet

                    </p>

                @endif

            </div>


            <!-- Withdrawal Rate -->

            <div class="bg-blue-50 border
                        border-blue-200 rounded-lg p-5">

                <p class="text-sm font-medium
                          text-blue-700">

                    Withdrawal Rate

                </p>

                <p class="text-2xl font-bold
                          text-blue-700 mt-2">

                    {{ number_format($withdrawalRate, 1) }}%

                </p>

                <p class="text-xs text-blue-600 mt-2">

                    Withdrawals vs deposits

                </p>

            </div>


            <!-- Latest Transaction -->

            <div class="bg-purple-50 border
                        border-purple-200 rounded-lg p-5">

                <p class="text-sm font-medium
                          text-purple-700">

                    Latest Activity

                </p>

                @if($latestTransaction)

                    <p class="text-lg font-bold
                              text-purple-700 mt-2">

                        {{ ucfirst($latestTransaction->type) }}

                    </p>

                    <p class="text-sm text-purple-600">

                        ₹ {{ number_format(
                            abs((float) $latestTransaction->amount),
                            2
                        ) }}

                    </p>

                @else

                    <p class="text-sm text-gray-500 mt-2">

                        No activity yet

                    </p>

                @endif

            </div>

        </div>


        <!-- Wallet Insight Message -->

        <div class="mt-6 bg-gray-50
                    border border-gray-200
                    rounded-lg p-4">

            <p class="text-sm text-gray-700">

                <strong>
                    Wallet Insight:
                </strong>

                {{ $walletInsight }}

            </p>

        </div>

    </div>


    <!-- ========================================================= -->
    <!-- MONTHLY SUMMARY -->
    <!-- ========================================================= -->

    <div class="bg-white rounded-xl shadow-sm
                border border-gray-200 p-6 mb-8">

        <h2 class="text-xl font-bold text-gray-800">
            Monthly Financial Summary
        </h2>

        <p class="text-sm text-gray-500 mb-6">
            Current month's wallet activity
        </p>


        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="bg-green-50 border
                        border-green-200 rounded-lg p-5">

                <p class="text-sm text-green-700 font-medium">

                    This Month Deposits

                </p>

                <p class="text-2xl font-bold
                          text-green-700 mt-2">

                    ₹ {{ number_format($monthlyDeposits, 2) }}

                </p>

            </div>


            <div class="bg-red-50 border
                        border-red-200 rounded-lg p-5">

                <p class="text-sm text-red-700 font-medium">

                    This Month Withdrawals

                </p>

                <p class="text-2xl font-bold
                          text-red-700 mt-2">

                    ₹ {{ number_format($monthlyWithdrawals, 2) }}

                </p>

            </div>

        </div>

    </div>


    <!-- ========================================================= -->
    <!-- DEPOSIT & WITHDRAW -->
    <!-- ========================================================= -->

    <div class="grid grid-cols-1 md:grid-cols-2
                gap-6 mb-8">

        <!-- Deposit -->

        <div class="bg-white rounded-xl shadow-sm
                    border border-gray-200 p-6">

            <h2 class="text-xl font-bold
                       text-green-600 mb-4">

                Add Money

            </h2>

            <p class="text-sm text-gray-500 mb-4">

                Add money to your wallet balance.

            </p>

            <form method="POST"
                  action="{{ route('wallet.deposit') }}">

                @csrf

                <input
                    type="number"
                    name="amount"
                    min="1"
                    max="1000000"
                    step="0.01"
                    placeholder="Enter amount"
                    value="{{ old('amount') }}"
                    class="w-full border border-gray-300
                           rounded-lg p-3 mb-4"
                    required
                >

                <button
                    type="submit"
                    class="w-full bg-green-500
                           hover:bg-green-600
                           text-white font-semibold
                           py-3 rounded-lg">

                    Deposit Money

                </button>

            </form>

        </div>


        <!-- Withdraw -->

        <div class="bg-white rounded-xl shadow-sm
                    border border-gray-200 p-6">

            <h2 class="text-xl font-bold
                       text-red-600 mb-4">

                Withdraw Money

            </h2>

            <p class="text-sm text-gray-500 mb-4">

                Withdraw money from your wallet.

            </p>

            <form method="POST"
                  action="{{ route('wallet.withdraw') }}">

                @csrf

                <input
                    type="number"
                    name="amount"
                    min="1"
                    max="1000000"
                    step="0.01"
                    placeholder="Enter amount"
                    class="w-full border border-gray-300
                           rounded-lg p-3 mb-4"
                    required
                >

                <button
                    type="submit"
                    class="w-full bg-red-500
                           hover:bg-red-600
                           text-white font-semibold
                           py-3 rounded-lg">

                    Withdraw Money

                </button>

            </form>

        </div>

    </div>


    <!-- ========================================================= -->
    <!-- TRANSACTION HISTORY -->
    <!-- ========================================================= -->

    <div class="bg-white rounded-xl shadow-sm
                border border-gray-200 p-6">

        <div class="flex flex-col md:flex-row
                    md:justify-between
                    md:items-center gap-4 mb-6">

            <div>

                <h2 class="text-xl font-bold text-gray-800">

                    Transaction History

                </h2>

                <p class="text-sm text-gray-500">

                    Search, filter and export wallet transactions.

                </p>

            </div>


            <!-- CSV Export -->

            <a
                href="{{ route('wallet.export', request()->query()) }}"
                class="inline-flex items-center
                       justify-center
                       bg-gray-800
                       hover:bg-gray-900
                       text-white px-5 py-2.5
                       rounded-lg font-semibold">

                Export CSV

            </a>

        </div>


        <!-- ===================================================== -->
        <!-- SEARCH & FILTER -->
        <!-- ===================================================== -->

        <form
            method="GET"
            action="{{ route('wallet.index') }}"
            class="bg-gray-50 border
                   border-gray-200
                   rounded-lg p-4 mb-6">

            <div class="grid grid-cols-1
                        md:grid-cols-2
                        lg:grid-cols-5 gap-4">

                <!-- Search -->

                <div class="lg:col-span-2">

                    <label class="block text-sm
                                  font-medium
                                  text-gray-700 mb-1">

                        Search

                    </label>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search type or amount"
                        class="w-full border
                               border-gray-300
                               rounded-lg p-2.5"
                    >

                </div>


                <!-- Type -->

                <div>

                    <label class="block text-sm
                                  font-medium
                                  text-gray-700 mb-1">

                        Type

                    </label>

                    <select
                        name="type"
                        class="w-full border
                               border-gray-300
                               rounded-lg p-2.5">

                        <option value="">
                            All Types
                        </option>

                        <option value="deposit"
                            {{ request('type') === 'deposit'
                                ? 'selected'
                                : '' }}>

                            Deposit

                        </option>

                        <option value="withdraw"
                            {{ request('type') === 'withdraw'
                                ? 'selected'
                                : '' }}>

                            Withdrawal

                        </option>

                    </select>

                </div>


                <!-- From Date -->

                <div>

                    <label class="block text-sm
                                  font-medium
                                  text-gray-700 mb-1">

                        From Date

                    </label>

                    <input
                        type="date"
                        name="from_date"
                        value="{{ request('from_date') }}"
                        class="w-full border
                               border-gray-300
                               rounded-lg p-2.5"
                    >

                </div>


                <!-- To Date -->

                <div>

                    <label class="block text-sm
                                  font-medium
                                  text-gray-700 mb-1">

                        To Date

                    </label>

                    <input
                        type="date"
                        name="to_date"
                        value="{{ request('to_date') }}"
                        class="w-full border
                               border-gray-300
                               rounded-lg p-2.5"
                    >

                </div>

            </div>


            <div class="flex flex-wrap gap-3 mt-4">

                <button
                    type="submit"
                    class="bg-blue-600
                           hover:bg-blue-700
                           text-white px-5 py-2.5
                           rounded-lg font-semibold">

                    Apply Filters

                </button>


                <a
                    href="{{ route('wallet.index') }}"
                    class="bg-gray-200
                           hover:bg-gray-300
                           text-gray-800 px-5 py-2.5
                           rounded-lg font-semibold">

                    Clear Filters

                </a>

            </div>

        </form>


        <!-- ===================================================== -->
        <!-- TRANSACTION TABLE -->
        <!-- ===================================================== -->

        <div class="overflow-x-auto">

            <table class="w-full border-collapse">

                <thead>

                    <tr class="bg-gray-100 text-gray-700">

                        <th class="border border-gray-200 p-3 text-left">
                            ID
                        </th>

                        <th class="border border-gray-200 p-3 text-left">
                            Type
                        </th>

                        <th class="border border-gray-200 p-3 text-right">
                            Amount
                        </th>

                        <th class="border border-gray-200 p-3 text-center">
                            Status
                        </th>

                        <th class="border border-gray-200 p-3 text-center">
                            Date
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($transactions as $transaction)

                        <tr class="hover:bg-gray-50">

                            <td class="border border-gray-200 p-3">

                                #{{ $transaction->id }}

                            </td>


                            <td class="border border-gray-200 p-3">

                                @if($transaction->type === 'deposit')

                                    <span class="inline-flex
                                                 px-3 py-1
                                                 rounded-full
                                                 text-xs font-semibold
                                                 bg-green-100
                                                 text-green-700">

                                        Deposit

                                    </span>

                                @else

                                    <span class="inline-flex
                                                 px-3 py-1
                                                 rounded-full
                                                 text-xs font-semibold
                                                 bg-red-100
                                                 text-red-700">

                                        Withdrawal

                                    </span>

                                @endif

                            </td>


                            <td class="border border-gray-200
                                       p-3 text-right">

                                @if($transaction->type === 'deposit')

                                    <span class="font-semibold
                                                 text-green-600">

                                        + ₹
                                        {{ number_format(
                                            abs((float) $transaction->amount),
                                            2
                                        ) }}

                                    </span>

                                @else

                                    <span class="font-semibold
                                                 text-red-600">

                                        - ₹
                                        {{ number_format(
                                            abs((float) $transaction->amount),
                                            2
                                        ) }}

                                    </span>

                                @endif

                            </td>


                            <td class="border border-gray-200
                                       p-3 text-center">

                                <span class="inline-flex
                                             px-3 py-1
                                             rounded-full
                                             text-xs font-semibold
                                             bg-blue-100
                                             text-blue-700">

                                    Completed

                                </span>

                            </td>


                            <td class="border border-gray-200
                                       p-3 text-center">

                                {{ $transaction->created_at
                                    ->format('d M Y H:i') }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5"
                                class="border border-gray-200
                                       p-8 text-center
                                       text-gray-500">

                                No transactions found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <!-- ===================================================== -->
        <!-- PAGINATION -->
        <!-- ===================================================== -->

        @if($transactions->hasPages())

            <div class="mt-6">

                {{ $transactions->links() }}

            </div>

        @endif

    </div>

@endsection