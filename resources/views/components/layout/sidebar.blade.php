{{--
@php
    $settings = \App\Models\SystemSetting::getSettings();
@endphp


@props(['active' => ''])

@php
    $navigation = [
        [
            'name' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'nav/dashboard',
        ],
        [
            'name' => 'Point of Sale',
            'route' => 'pos.index',
            'icon' => 'nav/cart',
        ],
        [
            'name' => 'Purchase',
            'route' => 'purchases.index',
            'icon' => 'nav/stock',
        ],
        [
            'name' => 'Inventory',
            'route' => 'inventory.index',
            'icon' => 'nav/stock',
        ],
        [
            'name' => 'Categories',
            'route' => 'categories.index',
            'icon' => 'nav/category',
        ],
        [
            'name' => 'Sales',
            'route' => 'sales.index',
            'icon' => 'nav/sale',
        ],
        [
            'name' => 'Reports',
            'route' => 'reports.index',
            'icon' => 'nav/report',
        ],
    ];
@endphp

<aside class="w-64 bg-white border-r border-gray-200 h-screen sticky top-0 flex flex-col">
    <!-- Logo -->
    <div class="p-6 border-b border-gray-200">
        <a href="{{route("dashboard")}}" class="flex items-center space-x-3">
            <div
                class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
                <i class="lni lni-capsule text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900">{{$settings['company_name']}}</h1>
                <p class="text-xs text-gray-500">Pharmacy System</p>
            </div>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6">
        <div class="space-y-2">
            @foreach($navigation as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center px-4 py-3 text-gray-700 rounded-md transition-all duration-200 group
                      {{ request()->routeIs($item['route'] . '*') ?
                         'bg-primary-50 text-primary-700 border-r-3 border-primary-500 shadow-sm' :
                         'hover:bg-gray-50 hover:text-gray-900' }}">
                    <x-ui.icon name="{{$item['icon']}}" class="w-5 h-5 mr-2" variant="success"></x-ui.icon>
                    <img src="" alt="" />
                    <span class="font-medium">{{ $item['name'] }}</span>

                    @if(request()->routeIs($item['route'] . '*'))
                        <div class="ml-auto w-2 h-4 bg-primary-500 rounded-full"></div>
                    @endif
                </a>
            @endforeach
        </div>
    </nav>

    <!-- User Section -->
    <div class="p-4 border-t border-gray-200">
        <div class="flex items-center space-x-3 p-3 rounded-xl bg-gray-50">
            <div
                class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-semibold text-sm">P</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">Pharmacist</p>
                <p class="text-xs text-gray-500 truncate">{{system_access(Auth()->user()->role->name)}}</p>
            </div>
            <a href="{{route('settings.system')}}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="lni lni-cog text-lg"></i>
            </a>
        </div>
    </div>
</aside>
--}}


@php
    $settings = \App\Models\SystemSetting::getSettings();
@endphp

@props(['active' => ''])

@php
    $navigation = [
        [
            'name' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'nav/dashboard',
            'type' => 'single',
        ],
        [
            'name' => 'Point of Sale',
            'route' => 'pos.index',
            'icon' => 'nav/cart',
            'type' => 'single',
        ],
        [
            'name' => 'Purchases',
            'icon' => 'nav/stock',
            'type' => 'group',
            'expanded' => request()->routeIs('purchases.*'),
            'children' => [
                [
                    'name' => 'All Orders',
                    'route' => 'purchases.index',
                ],
                [
                    'name' => 'New Order',
                    'route' => 'purchases.create',
                ],
            ],
        ],
        [
            'name' => 'Suppliers',
            'icon' => 'users',
            'type' => 'group',
            'expanded' => request()->routeIs('purchases.suppliers.*'),
            'children' => [
                [
                    'name' => 'All Suppliers',
                    'route' => 'purchases.suppliers.index'
                ],
                [
                    'name' => 'Create Supplier',
                    'route' => 'purchases.suppliers.create'
                ],
            ]
        ],
        [
            'name' => 'Inventory',
            'icon' => 'nav/stock',
            'type' => 'group',
            'expanded' => request()->routeIs('inventory.*') || request()->routeIs('categories.*'),
            'children' => [
                [
                    'name' => 'Products',
                    'route' => 'inventory.index',
                ],
                [
                    'name' => 'Categories',
                    'route' => 'categories.index',
                ],
            ],
        ],
        [
            'name' => 'Sales',
            'route' => 'sales.index',
            'icon' => 'nav/sale',
            'type' => 'single',
        ],
        [
            'name' => 'Reports',
            'icon' => 'nav/report',
            'type' => 'group',
            'expanded' => request()->routeIs('reports.*'),
            'children' => [
                [
                    'name' => 'Sales Reports',
                    'route' => 'reports.index',
                ],
                [
                    'name' => 'Sales Trends',
                    'route' => 'reports.sales-trends',
                ],
                [
                    'name' => 'Inventory Reports',
                    'route' => 'reports.inventory',
                ],
                [
                    'name' => 'Profit & Loss',
                    'route' => 'reports.profit-loss',
                ],
                [
                    'name' => 'Expiring Products',
                    'route' => 'reports.expiring-products',
                ],
            ],
        ],
        [
            'name' => 'Settings',
            'icon' => 'lni lni-cog',
            'type' => 'group',
            'expanded' => request()->routeIs('settings.*') || request()->routeIs('profile.*'),
            'children' => [
                [
                    'name' => 'System Settings',
                    'route' => 'settings.system',
                ],
                [
                    'name' => 'User Management',
                    'route' => 'settings.users',
                ],
                [
                    'name' => 'Roles & Permissions',
                    'route' => 'settings.roles',
                ],
                [
                    'name' => 'My Profile',
                    'route' => 'profile.edit',
                ],
            ],
        ],
    ];
@endphp

<aside class="bg-white border-r border-gray-200 h-screen flex flex-col transition-all duration-300"
        :class="{
            'w-64': sidebarOpen,
            'w-24': !sidebarOpen && !isMobile,
            'w-0': !sidebarOpen && isMobile,
            'fixed z-50': isMobile,
            'sticky': !isMobile,
            '-translate-x-full': !sidebarOpen && isMobile,
            'translate-x-0': sidebarOpen || !isMobile
        }"
        x-show="sidebarOpen || !isMobile"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        style="top: 0;">
    <!-- Logo -->
    <div class="p-6 border-b border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between">
            <a href="{{route("dashboard")}}" class="flex items-center space-x-3 min-w-0">
                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="lni lni-capsule text-white text-lg"></i>
                </div>
                <div x-show="sidebarOpen" x-transition class="min-w-0">
                    <h1 class="text-lg font-bold text-gray-900 truncate">{{$settings['company_name']}}</h1>
                    <p class="text-xs text-gray-500 truncate">Pharmacy System</p>
                </div>
            </a>
            <!-- Close button for mobile -->
            <button @click="sidebarOpen = false"
                    x-show="isMobile"
                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors flex-shrink-0">
                <i class="lni lni-close text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 overflow-y-auto">
        <div class="space-y-1">
        @foreach($navigation as $item)
            @if($item['type'] === 'single')
                <!-- Single Menu Item -->
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center px-4 py-3 text-gray-700 rounded-lg transition-all duration-200 group relative
                              {{ request()->routeIs($item['route'] . '*') ?
                                 'bg-primary-50 text-primary-700 border-r-3 border-primary-500 shadow-sm' :
                                 'hover:bg-gray-50 hover:text-gray-900' }}"
                       x-data="{ showTooltip: false }"
                       @mouseenter="if (!sidebarOpen && !isMobile) showTooltip = true"
                       @mouseleave="showTooltip = false">
                        @if(str_starts_with($item['icon'], 'lni'))
                            <i class="{{ $item['icon'] }} w-5 h-5 text-gray-500 group-hover:text-gray-700 {{ request()->routeIs($item['route'] . '*') ? 'text-primary-600' : '' }} flex-shrink-0" :class="{ 'mr-3': sidebarOpen }"></i>
                        @else
                            <div class="flex-shrink-0" :class="{ 'mr-3': sidebarOpen }">
                                <x-ui.icon name="{{$item['icon']}}" class="w-5 h-5" variant="success"></x-ui.icon>
                            </div>
                        @endif
                        <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">{{ $item['name'] }}</span>

                        @if(request()->routeIs($item['route'] . '*'))
                            <div x-show="sidebarOpen" class="ml-auto w-2 h-4 bg-primary-500 rounded-full"></div>
                        @endif

                        <!-- Tooltip for collapsed state -->
                        <div x-show="showTooltip && !sidebarOpen && !isMobile"
                             x-transition
                             class="absolute left-full ml-2 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg whitespace-nowrap z-50 shadow-lg">
                            {{ $item['name'] }}
                        </div>
                    </a>
            @elseif($item['type'] === 'group')
                <!-- Expandable Menu Group -->
                    <div x-data="{ open: {{ $item['expanded'] ? 'true' : 'false' }}, showTooltip: false }" class="rounded-lg overflow-hidden relative"
                         @mouseenter="if (!sidebarOpen && !isMobile) showTooltip = true"
                         @mouseleave="showTooltip = false">
                        <!-- Group Header -->
                        <button @click="if (sidebarOpen) open = !open; else sidebarOpen = true"
                                class="w-full flex items-center justify-between px-4 py-3 text-gray-700 rounded-lg transition-all duration-200 hover:bg-gray-50 hover:text-gray-900 group relative">
                            <div class="flex items-center">
                                @if(str_starts_with($item['icon'], 'lni'))
                                    <i class="{{ $item['icon'] }} w-5 h-5 text-gray-500 group-hover:text-gray-700 flex-shrink-0" :class="{ 'mr-3': sidebarOpen }"></i>
                                @else
                                    <div class="flex-shrink-0" :class="{ 'mr-3': sidebarOpen }">
                                        <x-ui.icon name="{{$item['icon']}}" class="w-5 h-5" variant="success"></x-ui.icon>
                                    </div>
                                @endif
                                <span x-show="sidebarOpen" x-transition class="font-medium whitespace-nowrap">{{ $item['name'] }}</span>
                            </div>
                            <i x-show="sidebarOpen" class="lni lni-chevron-down text-gray-400 text-xs transition-transform duration-200"
                               :class="{ 'rotate-180': open }"></i>
                        </button>

                        <!-- Tooltip for collapsed state -->
                        <div x-show="showTooltip && !sidebarOpen && !isMobile"
                             x-transition
                             class="absolute left-full ml-2 top-0 px-3 py-2 bg-gray-900 text-white text-sm rounded-lg whitespace-nowrap z-50 shadow-lg">
                            {{ $item['name'] }}
                        </div>

                        <!-- Submenu Items -->
                        <div x-show="open" x-collapse class="ml-10 mt-1 space-y-1">
                            @foreach($item['children'] as $child)
                                @php
                                    $isActive = isset($child['route']) ? request()->routeIs($child['route'] . '*') : false;
                                    $url = isset($child['route']) ? route($child['route']) : ($child['url'] ?? '#');
                                @endphp
                                <a href="{{ $url }}"
                                   class="block px-3 py-2 text-sm text-gray-600 rounded-lg transition-all duration-200
                                          {{ $isActive ? 'bg-primary-50 text-primary-700 font-medium' : 'hover:bg-gray-50 hover:text-gray-900' }}">
                                    <div class="flex items-center">
                                        <div class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-2 {{ $isActive ? 'bg-primary-500' : '' }}"></div>
                                        {{ $child['name'] }}
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </nav>

    <!-- User Section -->
    <div class="p-4 border-t border-gray-200 overflow-hidden">
        <div class="flex items-center p-3 rounded-xl bg-gray-50" :class="{ 'space-x-3': sidebarOpen, 'justify-center': !sidebarOpen }">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <span class="text-white font-semibold text-sm">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
            </div>
            <div x-show="sidebarOpen" x-transition class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->role->display_name ?? 'User' }}</p>
            </div>
            <div x-show="sidebarOpen" class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button @click="open = !open"
                        class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                    <i class="lni lni-cog text-lg"></i>
                </button>

                <!-- Quick Settings Dropdown -->
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute bottom-full right-0 mb-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50">

                    <a href="{{ route('settings.index') }}"
                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="lni lni-cog mr-3 text-gray-400"></i>
                        <span>Settings</span>
                    </a>

                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="lni lni-user mr-3 text-gray-400"></i>
                        <span>My Profile</span>
                    </a>

                    <div class="border-t border-gray-100 my-1"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex items-center w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            <i class="lni lni-exit mr-3"></i>
                            <span>Log Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>

@push('scripts')
    <script>
        // Initialize Alpine.js for menu interactions
        document.addEventListener('alpine:init', () => {
            // You can add any custom Alpine.js functionality here if needed
        });
    </script>
@endpush
