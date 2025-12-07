<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pharmacy Management System</title>

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
                        <h1 class="text-2xl font-bold">PharmacyPro</h1>
                        <p class="text-white/80">Management System</p>
                    </div>
                </div>
            </div>

            <!-- Hero Content -->
            <div class="max-w-md">
                <h2 class="text-4xl font-bold mb-4">Welcome Back</h2>
                <p class="text-lg text-white/90 mb-8">
                    Sign in to access your pharmacy management dashboard.
                </p>

                <!-- Features List -->
                <div class="space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="lni lni-checkmark"></i>
                        </div>
                        <span>Real-time inventory tracking</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="lni lni-checkmark"></i>
                        </div>
                        <span>Secure sales processing</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="lni lni-checkmark"></i>
                        </div>
                        <span>Detailed analytics & reports</span>
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
                    <h2 class="text-2xl font-bold text-gray-900">Sign in to your account</h2>
                    <p class="text-gray-600 mt-2">Enter your credentials to access the system</p>
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

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email -->
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

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="lni lni-lock text-gray-400"></i>
                            </div>
                            <input id="password"
                                   type="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input id="remember_me"
                                   type="checkbox"
                                   name="remember"
                                   class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <span class="ms-2 text-sm text-gray-600">Remember me</span>
                        </label>

                        {{--@if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                                Forgot password?
                            </a>
                        @endif--}}
                    </div>

                    <!-- Submit Button -->
                    <!-- Submit Button -->
                    <button type="submit"
                            id="loginButton"
                            class="w-full cursor-pointer bg-gradient-to-br from-primary-500 to-primary-600 text-white font-semibold py-3 px-4 rounded-lg hover:from-primary-600 hover:to-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all duration-200 flex items-center justify-center disabled:opacity-70 disabled:cursor-not-allowed">
                        <span id="buttonText" class="flex flex-row items-center gap-2">
                            <i class="lni lni-enter mr-2"></i>
                            Sign In
                        </span>
                        <span id="buttonSpinner" class="hidden flex flex-row items-center gap-2">
                            <i class="lni lni-spinner animate-spin mr-2"></i>
                            Signing in...
                        </span>
                    </button>


                </form>

                <!-- Footer Links -->
                {{--<div class="mt-8 pt-6 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-600">
                        Need help? <a href="#" class="text-primary-600 hover:text-primary-700 font-medium">Contact
                            support</a>
                    </p>
                </div>--}}
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    // Toggle password visibility (optional enhancement)
    document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.createElement('button');
        const loginForm = document.querySelector('form[action="{{ route("login") }}"]');
        togglePassword.type = 'button';
        togglePassword.className = 'absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600';
        togglePassword.innerHTML = '<i class="lni lni-eye"></i>';
        const buttonText = document.getElementById('buttonText');
        const buttonSpinner = document.getElementById('buttonSpinner');
        const loginButton = document.getElementById('loginButton');

        const passwordInput = document.getElementById('password');
        const passwordWrapper = passwordInput.parentElement;

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            this.innerHTML = type === 'password' ? '<i class="lni lni-eye"></i>' : '<i class="lni lni-eye-closed"></i>';
        });

        if (loginForm) {
            loginForm.addEventListener('submit', function () {
                // Show spinner, hide text
                buttonText.classList.add('hidden');
                buttonSpinner.classList.remove('hidden');

                // Disable button
                loginButton.disabled = true;
            });
        }

        passwordWrapper.classList.add('relative');
        passwordWrapper.appendChild(togglePassword);
    });
</script>
</body>
</html>
