<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WalletController extends Controller
{
    // Display wallet balance and transaction history
    public function index()
    {
        $user = auth()->user();

        $balance = $user->balance;
        $transactions = $user->transactions()->latest()->get();

        return view('wallet.index', compact('balance', 'transactions'));
    }

    // Add money to the authenticated user's wallet
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        auth()->user()->deposit($request->amount);

        return back()->with('success', 'Money Added Successfully');
    }

    // Withdraw money from the authenticated user's wallet
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $user = auth()->user();

        if (!$user->canWithdraw($request->amount)) {
            return back()->with('error', 'Insufficient Balance');
        }

        $user->withdraw($request->amount);

        return back()->with('success', 'Money Withdrawn Successfully');
    }
}
