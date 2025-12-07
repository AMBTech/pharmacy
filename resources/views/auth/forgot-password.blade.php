<?php
    $settings = \App\Models\SystemSetting::getSettings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password - {{$settings['company_name']}}</title>

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">

    <!-- LineIcons -->
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet"/>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .login-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .pharmacy-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M50 20v60M20 50h60' stroke='white' stroke-width='2' stroke-opacity='0.1'/%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="font-sans bg-gray-50 antialiased">
<div class="min-h-screen flex">
    <!-- Left Side - Image/Graphics -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary-500 to-primary-600 relative overflow-hidden">
        <div class="absolute inset-0 bg-black/10"></div>

        <div class="relative z-10 w-full flex flex-col justify-between p-12 text-white">
            <!-- Logo/Brand -->
            <div>
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                        <i class="lni lni-hospital text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">{{$settings['company_name']}}</h1>
                        <p class="text-white/80">Management System</p>
                    </div>
                </div>
            </div>

            <!-- Hero Content -->
            <div class="max-w-md">

                <div class="space-y-4">
                    <h2 class="text-4xl font-bold mb-4">Forgot Password?</h2>
                    <p class="text-lg text-white/90 mb-8">
                        No worries — enter your email and we’ll send you a password reset link.
                    </p>

                    <!-- Features List -->
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                                <i class="lni lni-envelope"></i>
                            </div>
                            <span>Receive a secure reset link via email</span>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                                <i class="lni lni-shield"></i>
                            </div>
                            <span>Your credentials remain fully protected</span>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                                <i class="lni lni-lock"></i>
                            </div>
                            <span>Quick and secure recovery process</span>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Footer -->
            <div class="text-white/70 text-sm">
                <p>© {{ date('Y') }} Pharmacy Management System. All rights reserved.</p>
            </div>
        </div>

        <!-- Decorative Elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-32 translate-x-32"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-white/5 rounded-full translate-y-48 -translate-x-48"></div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6">
        <div class="w-full max-w-md">
            <!-- Mobile Logo -->
            <div class="lg:hidden flex items-center justify-center mb-8">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
                        <i class="lni lni-hospital text-xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">PharmacyPro</h1>
                        <p class="text-sm text-gray-600">Management System</p>
                    </div>
                </div>
            </div>

            <!-- Login Form Card -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">Reset your password</h2>
                    <p class="text-gray-600 mt-2">Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.</p>
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                        {{ session('status') }}
                    </div>
                @endif

            <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="lni lni-envelope text-gray-400"></i>
                            </div>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   autocomplete="email"
                                   class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                   placeholder="you@example.com">
                        </div>
                    </div>

                    <button type="submit"
                            id="loginButton"
                            class="w-full cursor-pointer bg-gradient-to-br from-primary-500 to-primary-600 text-white font-semibold py-3 px-4 rounded-lg hover:from-primary-600 hover:to-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all duration-200 flex items-center justify-center disabled:opacity-70 disabled:cursor-not-allowed">
                        <span id="buttonText" class="flex flex-row items-center gap-2">
                            <i class="lni lni-link mr-2"></i>
                            Email Password Reset Link
                        </span>
                        <span id="buttonSpinner" class="hidden flex flex-row items-center gap-2">
                            <i class="lni lni-spinner animate-spin mr-2"></i>
                            Sending...
                        </span>
                    </button>

                </form>

                <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-600">
                        Back to <a href="{{route('login')}}" class="text-primary-600 hover:text-primary-700 font-medium">Login</a>
                    </p>
                </div>

            </div>

        </div>
    </div>
</div>

{{--<div class="mb-4 text-sm text-gray-600">
    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
</div>

<!-- Session Status -->
<x-auth-session-status class="mb-4" :status="session('status')"/>

<form method="POST" action="{{ route('password.email') }}">
@csrf

<!-- Email Address -->
    <div>
        <x-input-label for="email" :value="__('Email')"/>
        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                      autofocus/>
        <x-input-error :messages="$errors->get('email')" class="mt-2"/>
    </div>

    <div class="flex items-center justify-end mt-4">
        <x-primary-button>
            {{ __('Email Password Reset Link') }}
        </x-primary-button>
    </div>
</form>--}}
</body>
</html>
