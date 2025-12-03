@php
    $settings = \App\Models\SystemSetting::getSettings();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$settings['company_name'] ?? 'PharmaCare'}}</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- LineIcons -->
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-gray-50 antialiased" x-data="{ sidebarOpen: true, isMobile: window.innerWidth < 768 }" x-init="$watch('isMobile', value => { if (value) sidebarOpen = false; }); window.addEventListener('resize', () => { isMobile = window.innerWidth < 768; if (isMobile) sidebarOpen = false; })">
<div class="flex h-screen overflow-hidden relative">
    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen && isMobile"
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 md:hidden"></div>

    <!-- Sidebar Component -->
    <x-layout.sidebar />

    <!-- Main Content -->
    <main class="flex-1 overflow-auto transition-all duration-300" :class="{ 'ml-0': !sidebarOpen || isMobile, 'ml-0': sidebarOpen && !isMobile }">
        <!-- Top Bar -->
        {{--<header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 sticky top-0 z-40">
            <div class="flex-1 max-w-xl">
                <div class="relative">
                    <i class="lni lni-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text"
                           placeholder="Search..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-gray-50">
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <button class="relative p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors">
                    <x-ui.icon name="bell"></x-ui.icon>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-danger-500 rounded-full"></span>
                </button>

                <x-ui.button
                    variant="primary"
                    icon="lni lni-plus"
                    href="{{ route('pos.index') }}">
                    New Sale
                </x-ui.button>

                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold text-sm">P</span>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900 capitalize">{{optional(Auth()->user()->role)->name}}</p>
                        <p class="text-xs text-gray-500">{{optional(Auth()->user())->name}}</p>
                    </div>
                </div>
            </div>
        </header>--}}
        <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-6 sticky top-0 z-40">
            <div class="flex items-center space-x-4 flex-1 max-w-xl">
                <!-- Sidebar Toggle Button -->
                <button @click="sidebarOpen = !sidebarOpen"
                        class="p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="lni text-xl" :class="sidebarOpen ? 'lni-menu' : 'lni-menu'"></i>
                </button>

                {{--<div class="relative flex-1 md:flex">
                    <i class="lni lni-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text"
                           placeholder="Search..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-gray-50">
                </div>--}}
            </div>

            <div class="flex items-center space-x-4">

                <x-ui.button
                    variant="primary"
                    icon="lni lni-plus"
                    href="{{ route('pos.index') }}">
                    New Sale
                </x-ui.button>

                <!-- User Dropdown -->
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open"
                            class="flex items-center space-x-3 focus:outline-none hover:bg-gray-50 p-2 rounded-lg transition-colors">
                        <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                        <div class="text-right hidden md:block">
                            <p class="text-sm font-medium text-gray-900 capitalize">{{ Auth::user()->role->display_name ?? 'User' }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->name }}</p>
                        </div>
                        <i class="lni lni-chevron-down text-gray-400 text-sm" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50">

                        <!-- Profile -->
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="lni lni-user mr-3 text-gray-400"></i>
                            <span>My Profile</span>
                        </a>

                        <!-- Settings -->
                        <a href="{{ route('settings.system') }}"
                           class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="lni lni-cog mr-3 text-gray-400"></i>
                            <span>Settings</span>
                        </a>

                        <!-- Divider -->
                        <div class="border-t border-gray-100 my-1"></div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="flex items-center w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <i class="lni lni-exit mr-3"></i>
                                <span>Log Out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="p-6">
            @yield('content')
        </div>
    </main>
</div>

<!-- Stack for scripts -->
@stack('scripts')
</body>
</html>
