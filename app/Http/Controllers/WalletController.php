<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WalletSecurityService;
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
            ->oldest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Transaction Query
        |--------------------------------------------------------------------------
        */

        $transactionQuery = $user->transactions();

        /*
        |--------------------------------------------------------------------------
        | 1. Transaction Search
        |--------------------------------------------------------------------------
        |
        | Search by:
        | - Transaction ID
        | - Transaction type
        | - Transaction amount
        |
        */

        if ($request->filled('search')) {

            $search = trim($request->input('search'));

            $transactionQuery->where(function ($query) use ($search) {

                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%");

            });
        }

        /*
        |--------------------------------------------------------------------------
        | Transaction Type Filter
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('type') &&
            in_array($request->type, ['deposit', 'withdraw'], true)
        ) {

            $transactionQuery->where(
                'type',
                $request->type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Minimum Amount Filter
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('min_amount') &&
            is_numeric($request->min_amount)
        ) {

            $minAmount = max(
                0,
                (float) $request->min_amount
            );

            $transactionQuery->whereRaw(
                'ABS(amount) >= ?',
                [$minAmount]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Maximum Amount Filter
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('max_amount') &&
            is_numeric($request->max_amount)
        ) {

            $maxAmount = max(
                0,
                (float) $request->max_amount
            );

            $transactionQuery->whereRaw(
                'ABS(amount) <= ?',
                [$maxAmount]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 6 & 7. Quick Date Filters
        |--------------------------------------------------------------------------
        |
        | today
        | this_month
        |
        */

        if ($request->quick_date === 'today') {

            $transactionQuery->whereDate(
                'created_at',
                today()
            );

        } elseif ($request->quick_date === 'this_month') {

            $transactionQuery
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month);
        }

        /*
        |--------------------------------------------------------------------------
        | Custom From Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from_date')) {

            $transactionQuery->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Custom To Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('to_date')) {

            $transactionQuery->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Transaction Sorting
        |--------------------------------------------------------------------------
        */

        $sort = $request->input(
            'sort',
            'oldest'
        );

        switch ($sort) {

            case 'oldest':

                $transactionQuery
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc');

                break;

            case 'amount_low':

                $transactionQuery
                    ->orderByRaw('ABS(amount) ASC')
                    ->orderBy('id', 'asc');

                break;

            case 'amount_high':

                $transactionQuery
                    ->orderByRaw('ABS(amount) DESC')
                    ->orderBy('id', 'desc');

                break;

            case 'id_asc':

                $transactionQuery
                    ->orderBy('id', 'asc');

                break;

            case 'id_desc':

                $transactionQuery
                    ->orderBy('id', 'desc');

                break;

            default:

                $sort = 'oldest';

                $transactionQuery
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc');

                break;
        }

        /*
        |--------------------------------------------------------------------------
        | 8. Filtered Transaction Count
        |--------------------------------------------------------------------------
        */

        $filteredTransactionCount =
            (clone $transactionQuery)->count();

        /*
        |--------------------------------------------------------------------------
        | 9. Filtered Deposit Total
        |--------------------------------------------------------------------------
        */

        $filteredDepositTotal =
            (clone $transactionQuery)
                ->where('type', 'deposit')
                ->get()
                ->sum(function ($transaction) {

                    return abs(
                        (float) $transaction->amount
                    );

                });

        /*
        |--------------------------------------------------------------------------
        | 10. Filtered Withdrawal Total
        |--------------------------------------------------------------------------
        */

        $filteredWithdrawalTotal =
            (clone $transactionQuery)
                ->where('type', 'withdraw')
                ->get()
                ->sum(function ($transaction) {

                    return abs(
                        (float) $transaction->amount
                    );

                });

        /*
        |--------------------------------------------------------------------------
        | 5. Transactions Per Page
        |--------------------------------------------------------------------------
        */

        $allowedPerPage = [
            5,
            10,
            25,
            50,
        ];

        $perPage = (int) $request->input(
            'per_page',
            5
        );

        if (!in_array($perPage, $allowedPerPage, true)) {

            $perPage = 5;
        }

        /*
        |--------------------------------------------------------------------------
        | Paginated Transactions
        |--------------------------------------------------------------------------
        */

        $transactions = $transactionQuery
            ->paginate($perPage)
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
        | Highest Deposit
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | Highest Withdrawal
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | Withdrawal Rate
        |--------------------------------------------------------------------------
        */

        $withdrawalRate = 0;

        if ($totalDeposits > 0) {

            $withdrawalRate =
                (
                    $totalWithdrawals /
                    $totalDeposits
                ) * 100;
        }

        /*
        |--------------------------------------------------------------------------
        | Latest Transaction
        |--------------------------------------------------------------------------
        */

        $latestTransaction = $allTransactions->first();

        /*
        |--------------------------------------------------------------------------
        | Low Balance Warning
        |--------------------------------------------------------------------------
        */

        $lowBalanceThreshold = 500;

        $isLowBalance =
            $balance < $lowBalanceThreshold;

        /*
        |--------------------------------------------------------------------------
        | Large Withdrawal Alert
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | Wallet Activity Insight
        |--------------------------------------------------------------------------
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

        return view(
            'wallet.index',
            compact(

                'balance',

                'transactions',

                'totalDeposits',
                'totalWithdrawals',
                'depositCount',
                'withdrawalCount',
                'transactionCount',

                'monthlyDeposits',
                'monthlyWithdrawals',

                'highestDeposit',
                'highestDepositAmount',
                'highestWithdrawal',
                'highestWithdrawalAmount',
                'withdrawalRate',
                'latestTransaction',

                'lowBalanceThreshold',
                'isLowBalance',
                'largeWithdrawalThreshold',
                'largeWithdrawal',

                'walletInsight',

                'filteredTransactionCount',
                'filteredDepositTotal',
                'filteredWithdrawalTotal',

                'perPage',
                'sort'
            )
        );
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
    public function withdraw(Request $request, WalletSecurityService $securityService)
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
        $amount = (float)$request->amount;

        $check = $securityService->validateWithdrawalPolicy($user, $amount);
        if (!$check['allowed']) {
            return back()->with('error', $check['message']);
        }

        $user->withdraw($amount);

        return back()->with('success', 'Money Withdrawn Successfully');
    }


    /**
     * Export wallet transactions as CSV.
     *
     * The same filters currently selected on the
     * wallet page are applied to the CSV export.
     */
    public function export(Request $request): Response
    {
        $user = $request->user();

        $transactionQuery = $user->transactions();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim(
                $request->input('search')
            );

            $transactionQuery->where(function ($query) use ($search) {

                $query->where(
                    'id',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
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
        | Type
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('type') &&
            in_array(
                $request->type,
                ['deposit', 'withdraw'],
                true
            )
        ) {

            $transactionQuery->where(
                'type',
                $request->type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Minimum Amount
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('min_amount') &&
            is_numeric($request->min_amount)
        ) {

            $minAmount = max(
                0,
                (float) $request->min_amount
            );

            $transactionQuery->whereRaw(
                'ABS(amount) >= ?',
                [$minAmount]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum Amount
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('max_amount') &&
            is_numeric($request->max_amount)
        ) {

            $maxAmount = max(
                0,
                (float) $request->max_amount
            );

            $transactionQuery->whereRaw(
                'ABS(amount) <= ?',
                [$maxAmount]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Quick Date Filter
        |--------------------------------------------------------------------------
        */

        if ($request->quick_date === 'today') {

            $transactionQuery->whereDate(
                'created_at',
                today()
            );

        } elseif ($request->quick_date === 'this_month') {

            $transactionQuery
                ->whereYear(
                    'created_at',
                    now()->year
                )
                ->whereMonth(
                    'created_at',
                    now()->month
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Custom Date Filters
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

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $sort = $request->input(
            'sort',
            'latest'
        );

        switch ($sort) {

            case 'oldest':

                $transactionQuery
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc');

                break;

            case 'amount_low':

                $transactionQuery
                    ->orderByRaw('ABS(amount) ASC')
                    ->orderBy('id', 'asc');

                break;

            case 'amount_high':

                $transactionQuery
                    ->orderByRaw('ABS(amount) DESC')
                    ->orderBy('id', 'desc');

                break;

            case 'id_asc':

                $transactionQuery
                    ->orderBy('id', 'asc');

                break;

            case 'id_desc':

                $transactionQuery
                    ->orderBy('id', 'desc');

                break;

            default:

                $transactionQuery
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc');

                break;
        }

        $transactions =
            $transactionQuery->get();

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

    /**
     * Display P2P Transfer view.
     */
    public function transferView(Request $request)
    {
        $user = $request->user();
        $recentTransfers = $user->transactions()
            ->where('type', 'transfer')
            ->latest()
            ->limit(5)
            ->get();

        return view('wallet.transfer', compact('user', 'recentTransfers'));
    }

    /**
     * Store P2P Transfer.
     */
    public function transferStore(Request $request, WalletSecurityService $securityService)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'amount' => 'required|numeric|min:0.01|max:100000',
        ]);

        $sender = $request->user();
        $recipient = User::where('email', $request->input('email'))->first();
        $amount = (float)$request->input('amount');

        $check = $securityService->validateTransferPolicy($sender, $recipient, $amount);

        if (!$check['allowed']) {
            return back()->with('error', $check['message']);
        }

        // Execute P2P Transfer using bavix/laravel-wallet transfer mechanism
        $sender->transfer($recipient, $amount);

        $msg = "Successfully transferred $" . number_format($amount, 2) . " to {$recipient->name} ({$recipient->email}).";
        if (!empty($check['is_high_value'])) {
            $msg .= " [High-Value Transfer Safety Alert]";
        }

        return redirect()->route('wallet.transfer')->with('success', $msg);
    }

    /**
     * Display Financial Analytics Dashboard.
     */
    public function analyticsView(Request $request, WalletSecurityService $securityService)
    {
        $user = $request->user();
        $analytics = $securityService->getWalletAnalyticsData($user);

        return view('wallet.analytics', compact('user', 'analytics'));
    }

    /**
     * Return JSON data for analytics charts.
     */
    public function analyticsData(Request $request, WalletSecurityService $securityService)
    {
        $user = $request->user();
        return response()->json($securityService->getWalletAnalyticsData($user));
    }
}
