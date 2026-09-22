<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WalletController extends Controller
{
    /**
     * Display wallet dashboard with analytics,
     * transaction filtering and wallet insights.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Wallet Balance
        |--------------------------------------------------------------------------
        */

        $balance = $user->balance;

        /*
        |--------------------------------------------------------------------------
        | All Transactions
        |--------------------------------------------------------------------------
        */

        $allTransactions = $user->transactions()
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Transaction Search & Filtering
        |--------------------------------------------------------------------------
        */

        $transactionQuery = $user->transactions()
            ->latest();

        // Search by transaction type or amount
        if ($request->filled('search')) {

            $search = $request->input('search');

            $transactionQuery->where(function ($query) use ($search) {

                $query->where(
                    'type',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'amount',
                    'like',
                    "%{$search}%"
                );

            });
        }

        // Filter by transaction type
        if (
            $request->filled('type') &&
            in_array($request->type, ['deposit', 'withdraw'])
        ) {

            $transactionQuery->where(
                'type',
                $request->type
            );
        }

        // Filter from date
        if ($request->filled('from_date')) {

            $transactionQuery->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }

        // Filter to date
        if ($request->filled('to_date')) {

            $transactionQuery->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Paginated Transactions
        |--------------------------------------------------------------------------
        */

        $transactions = $transactionQuery
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Basic Wallet Analytics
        |--------------------------------------------------------------------------
        */

        $totalDeposits = $allTransactions
            ->where('type', 'deposit')
            ->sum(function ($transaction) {

                return abs(
                    (float) $transaction->amount
                );

            });

        $totalWithdrawals = $allTransactions
            ->where('type', 'withdraw')
            ->sum(function ($transaction) {

                return abs(
                    (float) $transaction->amount
                );

            });

        $depositCount = $allTransactions
            ->where('type', 'deposit')
            ->count();

        $withdrawalCount = $allTransactions
            ->where('type', 'withdraw')
            ->count();

        $transactionCount = $allTransactions->count();

        /*
        |--------------------------------------------------------------------------
        | Monthly Statistics
        |--------------------------------------------------------------------------
        */

        $currentMonthTransactions = $allTransactions
            ->filter(function ($transaction) {

                return $transaction->created_at
                    ->isSameMonth(now());

            });

        $monthlyDeposits = $currentMonthTransactions
            ->where('type', 'deposit')
            ->sum(function ($transaction) {

                return abs(
                    (float) $transaction->amount
                );

            });

        $monthlyWithdrawals = $currentMonthTransactions
            ->where('type', 'withdraw')
            ->sum(function ($transaction) {

                return abs(
                    (float) $transaction->amount
                );

            });

        /*
        |--------------------------------------------------------------------------
        | WALLET TRANSACTION INSIGHTS
        |--------------------------------------------------------------------------
        */

        /*
        |----------------------------------------------------------------------
        | Highest Deposit
        |----------------------------------------------------------------------
        */

        $highestDeposit = $allTransactions
            ->where('type', 'deposit')
            ->sortByDesc(function ($transaction) {

                return abs(
                    (float) $transaction->amount
                );

            })
            ->first();

        $highestDepositAmount = $highestDeposit
            ? abs((float) $highestDeposit->amount)
            : 0;

        /*
        |----------------------------------------------------------------------
        | Highest Withdrawal
        |----------------------------------------------------------------------
        */

        $highestWithdrawal = $allTransactions
            ->where('type', 'withdraw')
            ->sortByDesc(function ($transaction) {

                return abs(
                    (float) $transaction->amount
                );

            })
            ->first();

        $highestWithdrawalAmount = $highestWithdrawal
            ? abs((float) $highestWithdrawal->amount)
            : 0;

        /*
        |----------------------------------------------------------------------
        | Withdrawal Rate
        |----------------------------------------------------------------------
        |
        | Shows what percentage of deposited money has been withdrawn.
        |
        */

        $withdrawalRate = 0;

        if ($totalDeposits > 0) {

            $withdrawalRate = (
                $totalWithdrawals /
                $totalDeposits
            ) * 100;

        }

        /*
        |----------------------------------------------------------------------
        | Latest Transaction
        |----------------------------------------------------------------------
        */

        $latestTransaction = $allTransactions->first();

        /*
        |----------------------------------------------------------------------
        | Low Balance Warning
        |----------------------------------------------------------------------
        |
        | Balance below ₹500 is considered a low balance.
        |
        */

        $lowBalanceThreshold = 500;

        $isLowBalance = $balance < $lowBalanceThreshold;

        /*
        |----------------------------------------------------------------------
        | Large Withdrawal Alert
        |----------------------------------------------------------------------
        |
        | A withdrawal of ₹5,000 or more is highlighted.
        |
        */

        $largeWithdrawalThreshold = 5000;

        $largeWithdrawal = $allTransactions
            ->where('type', 'withdraw')
            ->filter(function ($transaction) use (
                $largeWithdrawalThreshold
            ) {

                return abs(
                    (float) $transaction->amount
                ) >= $largeWithdrawalThreshold;

            })
            ->first();

        /*
        |----------------------------------------------------------------------
        | Wallet Activity Insight
        |----------------------------------------------------------------------
        */

        if ($transactionCount === 0) {

            $walletInsight =
                'No wallet activity yet. Start by adding money to your wallet.';

        } elseif ($totalWithdrawals > $totalDeposits) {

            $walletInsight =
                'Your total withdrawals are higher than your deposits. Review your recent wallet activity.';

        } elseif ($withdrawalRate >= 75) {

            $walletInsight =
                'A large portion of your deposited funds has been withdrawn.';

        } elseif ($withdrawalRate >= 50) {

            $walletInsight =
                'More than half of your deposited funds have been withdrawn.';

        } elseif ($totalDeposits > $totalWithdrawals) {

            $walletInsight =
                'Your wallet currently has more deposits than withdrawals.';

        } else {

            $walletInsight =
                'Your wallet activity is currently balanced.';

        }

        /*
        |--------------------------------------------------------------------------
        | Return Wallet Dashboard
        |--------------------------------------------------------------------------
        */

        return view('wallet.index', compact(

            // Wallet
            'balance',

            // Transactions
            'transactions',

            // Analytics
            'totalDeposits',
            'totalWithdrawals',
            'depositCount',
            'withdrawalCount',
            'transactionCount',

            // Monthly
            'monthlyDeposits',
            'monthlyWithdrawals',

            // Insights
            'highestDeposit',
            'highestDepositAmount',
            'highestWithdrawal',
            'highestWithdrawalAmount',
            'withdrawalRate',
            'latestTransaction',

            // Alerts
            'lowBalanceThreshold',
            'isLowBalance',
            'largeWithdrawalThreshold',
            'largeWithdrawal',

            // Insight
            'walletInsight'
        ));
    }


    /**
     * Deposit money into wallet.
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:1',
                'max:1000000',
            ],
        ]);

        $request->user()->deposit(
            $request->amount
        );

        return back()->with(
            'success',
            'Money Added Successfully'
        );
    }


    /**
     * Withdraw money from wallet.
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:1',
                'max:1000000',
            ],
        ]);

        $user = $request->user();

        if (!$user->canWithdraw($request->amount)) {

            return back()->with(
                'error',
                'Insufficient Balance'
            );
        }

        $user->withdraw(
            $request->amount
        );

        return back()->with(
            'success',
            'Money Withdrawn Successfully'
        );
    }


    /**
     * Export wallet transactions as CSV.
     */
    public function export(Request $request): Response
    {
        $user = $request->user();

        $transactionQuery = $user->transactions()
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->input('search');

            $transactionQuery->where(function ($query) use ($search) {

                $query->where(
                    'type',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'amount',
                    'like',
                    "%{$search}%"
                );

            });
        }

        /*
        |--------------------------------------------------------------------------
        | Type Filter
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('type') &&
            in_array($request->type, ['deposit', 'withdraw'])
        ) {

            $transactionQuery->where(
                'type',
                $request->type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from_date')) {

            $transactionQuery->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }

        if ($request->filled('to_date')) {

            $transactionQuery->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }

        $transactions = $transactionQuery->get();

        /*
        |--------------------------------------------------------------------------
        | CSV Filename
        |--------------------------------------------------------------------------
        */

        $filename =
            'wallet-statement-' .
            now()->format('Y-m-d-H-i-s') .
            '.csv';

        /*
        |--------------------------------------------------------------------------
        | Create CSV
        |--------------------------------------------------------------------------
        */

        $handle = fopen(
            'php://temp',
            'r+'
        );

        /*
        |--------------------------------------------------------------------------
        | CSV Header
        |--------------------------------------------------------------------------
        */

        fputcsv($handle, [
            'Transaction ID',
            'Type',
            'Amount',
            'Date',
            'Status',
        ]);

        /*
        |--------------------------------------------------------------------------
        | CSV Data
        |--------------------------------------------------------------------------
        */

        foreach ($transactions as $transaction) {

            fputcsv($handle, [

                $transaction->id,

                ucfirst(
                    $transaction->type
                ),

                number_format(
                    abs(
                        (float) $transaction->amount
                    ),
                    2,
                    '.',
                    ''
                ),

                $transaction->created_at
                    ->format('d-m-Y H:i:s'),

                'Completed',

            ]);
        }

        rewind($handle);

        $csv = stream_get_contents(
            $handle
        );

        fclose($handle);

        return response(
            $csv,
            200,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',

                'Content-Disposition' =>
                    'attachment; filename="' .
                    $filename .
                    '"',
            ]
        );
    }
}