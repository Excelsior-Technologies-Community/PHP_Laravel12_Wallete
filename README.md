#  PHP_Laravel12_Wallete

![Laravel](https://img.shields.io/badge/Laravel-12.x-red)
![PHP](https://img.shields.io/badge/PHP-8.x-blue)
![Breeze](https://img.shields.io/badge/Auth-Laravel%20Breeze-green)
![Wallet](https://img.shields.io/badge/Wallet-bavix%2Flaravel--wallet-orange)
![License](https://img.shields.io/badge/License-MIT-lightgrey)

---

##  Overview

This project is a **Wallet Management System** built with **Laravel 12**.
It provides authentication and a complete digital wallet system where users can:

* Register & Login
* Deposit money
* Withdraw money
* View wallet balance
* View transaction history

The wallet functionality is powered by the `bavix/laravel-wallet` package.

---

##  Features

*  Laravel Breeze Authentication (Login/Register)
*  User Wallet Creation (Auto-created per user)
*  Deposit Money
*  Withdraw Money
*  Real-time Wallet Balance
*  Transaction History
*  Separate Wallet Layout
*  Auth-Protected Wallet Routes

---

##  Folder Structure

```
wallet-project/
│
├── app/
│   ├── Http/Controllers/
│   │   └── WalletController.php
│   └── Models/
│       └── User.php
│
├── resources/views/
│   ├── layouts/
│   │   └── wallet.blade.php
│   └── wallet/
│       └── index.blade.php
│
├── routes/
│   └── web.php
│
├── database/migrations/
│
└── .env
```

---

# 1. Project Installation

## Step 1: Create New Laravel Project

```bash
composer create-project laravel/laravel wallet-project
```

---

## Step 2: Configure Database

Open `.env` file and update:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

Then run:

```bash
php artisan migrate
```

---

# 2. Install Authentication (Laravel Breeze)

```bash
composer require laravel/breeze --dev

php artisan breeze:install

npm install

npm run dev

php artisan migrate
```

Now authentication (login/register) is ready.

---

# 3. Install Wallet Package

Install wallet package:

```bash
composer require bavix/laravel-wallet
```

Publish package files:

```bash
php artisan vendor:publish --provider="Bavix\Wallet\WalletServiceProvider"
```

Run migrations:

```bash
php artisan migrate
```

This creates the following tables:

* wallets
* transactions
* transfers

---

# 4. Configure User Model

Open:

```
app/Models/User.php
```

Replace the file with:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Wallet
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Authenticatable implements Wallet
{
    use HasFactory, Notifiable, HasWallet;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

Now every user automatically has a wallet.

---

# 5. Create Wallet Controller

Run:

```bash
php artisan make:controller WalletController
```

Open:

```
app/Http/Controllers/WalletController.php
```

```php
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
```

---

# 6. Add Web Routes

Open:

```
routes/web.php
```

Add:

```php
<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// Display welcome page
Route::get('/', function () {
    return view('welcome');
});

// Display dashboard page for authenticated and verified users
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile management routes (only for authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit'); // Show profile edit form
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); // Update profile information
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); // Delete user account
});

// Wallet routes (only for authenticated users)
Route::middleware(['auth'])->group(function () {
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index'); // Show wallet page
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit'); // Handle deposit request
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw'); // Handle withdraw request
});

// Authentication routes (login, register, logout, etc.)
require __DIR__.'/auth.php';
```

---

# 7. Create Custom Wallet Layout

Create new layout file:

```
resources/views/layouts/wallet.blade.php
```

```html
<!DOCTYPE html>
<html>
<head>
    <title>Wallet Page</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">

<div class="max-w-5xl mx-auto mt-10 bg-white shadow-lg rounded-lg p-6">
    @yield('content')
</div>

</body>
</html>
```

This layout makes wallet page independent from dashboard layout.

---

# 8. Create Wallet View

Create folder and file:

```
resources/views/wallet/index.blade.php
```

```blade
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
```

---

# Final Result

After login, open:

```
http://127.0.0.1:8000/wallet
```
<img width="1022" height="495" alt="Screenshot 2026-02-18 115916" src="https://github.com/user-attachments/assets/998aa9b7-59fb-42a8-a907-b7df5caa01cc" />


You now have:

## Deposit system :

<img width="1032" height="496" alt="Screenshot 2026-02-18 115938" src="https://github.com/user-attachments/assets/080d0c37-7276-4136-8337-48d2f6eb0595" />

<img width="1028" height="539" alt="Screenshot 2026-02-18 120009" src="https://github.com/user-attachments/assets/3d7112d9-f195-4680-8b9c-2af166760bf9" />

## Withdraw system:

<img width="1028" height="477" alt="Screenshot 2026-02-18 120031" src="https://github.com/user-attachments/assets/f930a81c-021a-458a-a706-3d30b253d7fd" />

<img width="1022" height="582" alt="Screenshot 2026-02-18 120049" src="https://github.com/user-attachments/assets/4ee9c606-3a18-4207-bd71-84409c157187" />

## Transaction history:

<img width="1033" height="291" alt="Screenshot 2026-02-18 122358" src="https://github.com/user-attachments/assets/6debe25a-fad5-4d9e-8925-2d4f6814d539" />



