@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
                <p class="text-gray-600 mt-1">Manage system configuration and users</p>
            </div>
        </div>

        <!-- Settings Navigation -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <a href="{{ route('settings.system') }}"
                       class="py-4 px-6 border-b-2 font-medium text-sm {{ request()->routeIs('settings.system*') ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="lni lni-cog mr-2"></i>
                        System Settings
                    </a>
                    <a href="{{ route('settings.users') }}"
                       class="py-4 px-6 border-b-2 font-medium text-sm {{ request()->routeIs('settings.users*') ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="lni lni-users mr-2"></i>
                        User Management
                    </a>
                    <a href="{{ route('settings.roles') }}"
                       class="py-4 px-6 border-b-2 font-medium text-sm {{ request()->routeIs('settings.roles*') ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="lni lni-shield mr-2"></i>
                        Roles & Permissions
                    </a>
                    <a href="{{ route('settings.storage-locations') }}"
                       class="py-4 px-6 border-b-2 font-medium text-sm {{ request()->routeIs('settings.storage-locations*') ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="lni lni-package mr-2"></i>
                        Storage Locations
                    </a>
                </nav>
            </div>

            <!-- Settings Content -->
            <div class="p-6">
                @yield('settings-content')
            </div>
        </div>
    </div>
@endsection
