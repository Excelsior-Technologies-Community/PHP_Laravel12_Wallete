<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>
        {{ config('app.name', 'Laravel') }} - Wallet
    </title>

    <!-- Fonts -->
    <link rel="preconnect"
          href="https://fonts.bunny.net">

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
          rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100 text-gray-900">

    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 shadow-sm">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex justify-between items-center h-16">

                <!-- Logo -->
                <div class="flex items-center">

                    <a href="{{ route('dashboard') }}"
                       class="text-xl font-bold text-gray-800">

                        Wallet Management

                    </a>

                </div>

                <!-- Navigation -->
                <div class="flex items-center gap-4">

                    <a href="{{ route('dashboard') }}"
                       class="text-sm text-gray-600 hover:text-gray-900">

                        Dashboard

                    </a>

                    <a href="{{ route('wallet.index') }}"
                       class="text-sm font-semibold text-blue-600">

                        My Wallet

                    </a>

                    <a href="{{ route('profile.edit') }}"
                       class="text-sm text-gray-600 hover:text-gray-900">

                        Profile

                    </a>

                    <!-- Logout -->
                    <form method="POST"
                          action="{{ route('logout') }}">

                        @csrf

                        <button type="submit"
                                class="text-sm text-red-600 hover:text-red-800">

                            Logout

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </nav>

    <!-- Wallet Content -->

    <main class="py-8 px-4">

        <div class="max-w-7xl mx-auto">

            @yield('content')

        </div>

    </main>

</body>

</html>