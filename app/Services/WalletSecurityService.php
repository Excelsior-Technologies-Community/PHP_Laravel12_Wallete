<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class WalletSecurityService
{
    /**
     * Daily transaction/withdrawal limit in wallet currency unit.
     */
    public const DAILY_SPENDING_LIMIT = 5000.00;

    /**
     * High value transaction threshold for security warning logs.
     */
    public const HIGH_VALUE_THRESHOLD = 1000.00;

    /**
     * Validate P2P Transfer Policy.
     */
    public function validateTransferPolicy(User $sender, User $recipient, float $amount): array
    {
        if ($sender->id === $recipient->id) {
            return [
                'allowed' => false,
                'message' => 'You cannot transfer funds to your own wallet account.',
            ];
        }

        if ($sender->balance < $amount) {
            return [
                'allowed' => false,
                'message' => 'Insufficient wallet balance for this transfer.',
            ];
        }

        // Daily spending check
        $todaySpent = $sender->transactions()
            ->where('created_at', '>=', now()->startOfDay())
            ->whereIn('type', ['withdraw', 'transfer'])
            ->get()
            ->sum(fn($t) => abs((float)$t->amount));

        if (($todaySpent + $amount) > self::DAILY_SPENDING_LIMIT) {
            $remaining = max(0, self::DAILY_SPENDING_LIMIT - $todaySpent);
            return [
                'allowed' => false,
                'message' => "Security Policy Violation: Exceeds daily spending limit of $" . number_format(self::DAILY_SPENDING_LIMIT, 2) . ". Remaining limit today: $" . number_format($remaining, 2),
            ];
        }

        $isHighValue = $amount >= self::HIGH_VALUE_THRESHOLD;

        return [
            'allowed' => true,
            'is_high_value' => $isHighValue,
            'message' => $isHighValue ? "High-value transfer alert triggered ($" . number_format($amount, 2) . ")." : "Transfer approved.",
        ];
    }

    /**
     * Validate Withdrawal Policy.
     */
    public function validateWithdrawalPolicy(User $user, float $amount): array
    {
        if ($user->balance < $amount) {
            return [
                'allowed' => false,
                'message' => 'Insufficient balance for withdrawal.',
            ];
        }

        $todaySpent = $user->transactions()
            ->where('created_at', '>=', now()->startOfDay())
            ->whereIn('type', ['withdraw', 'transfer'])
            ->get()
            ->sum(fn($t) => abs((float)$t->amount));

        if (($todaySpent + $amount) > self::DAILY_SPENDING_LIMIT) {
            $remaining = max(0, self::DAILY_SPENDING_LIMIT - $todaySpent);
            return [
                'allowed' => false,
                'message' => "Security Policy Violation: Daily limit ($" . number_format(self::DAILY_SPENDING_LIMIT, 2) . ") exceeded. Remaining limit today: $" . number_format($remaining, 2),
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Withdrawal approved.',
        ];
    }

    /**
     * Get wallet financial analytics and cash-flow data.
     */
    public function getWalletAnalyticsData(User $user): array
    {
        $allTx = $user->transactions()->oldest()->get();

        $totalDeposits = $allTx->where('type', 'deposit')->sum(fn($t) => abs((float)$t->amount));
        $totalWithdrawals = $allTx->where('type', 'withdraw')->sum(fn($t) => abs((float)$t->amount));
        $totalTransfers = $allTx->where('type', 'transfer')->sum(fn($t) => abs((float)$t->amount));

        $cashIn = $totalDeposits;
        $cashOut = $totalWithdrawals + $totalTransfers;

        // 7-day cash flow timeline
        $trendLabels = [];
        $cashInTrend = [];
        $cashOutTrend = [];

        for ($i = 6; $i >= 0; $i--) {
            $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
            $label = Carbon::now()->subDays($i)->format('M d');

            $dayTx = $allTx->filter(fn($t) => $t->created_at->format('Y-m-d') === $dateStr);

            $in = $dayTx->where('type', 'deposit')->sum(fn($t) => abs((float)$t->amount));
            $out = $dayTx->whereIn('type', ['withdraw', 'transfer'])->sum(fn($t) => abs((float)$t->amount));

            $trendLabels[] = $label;
            $cashInTrend[] = round($in, 2);
            $cashOutTrend[] = round($out, 2);
        }

        return [
            'balance' => (float)$user->balance,
            'cash_in' => round($cashIn, 2),
            'cash_out' => round($cashOut, 2),
            'total_deposits' => round($totalDeposits, 2),
            'total_withdrawals' => round($totalWithdrawals, 2),
            'total_transfers' => round($totalTransfers, 2),
            'trend_labels' => $trendLabels,
            'cash_in_trend' => $cashInTrend,
            'cash_out_trend' => $cashOutTrend,
        ];
    }
}
