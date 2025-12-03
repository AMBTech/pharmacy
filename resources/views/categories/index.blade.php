@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Product Categories</h1>
                <p class="text-gray-600 mt-1">Manage product categories and organization</p>
            </div>
            <a href="{{ route('categories.create') }}"
               class="bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors flex items-center">
                <i class="lni lni-plus mr-2"></i>
                Add New Category
            </a>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($categories as $category)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <!-- Category Header -->
                    <div class="p-6 border-b border-gray-200" style="border-left: 4px solid {{ $category->color }};">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $category->name }}</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>
                        </div>

                        @if($category->description)
                            <p class="text-sm text-gray-600 mb-4">{{ Str::limit($category->description, 100) }}</p>
                        @endif

                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>Sort Order: {{ $category->sort_order }}</span>
                            <span class="flex flex-row items-center gap-2">Color: <div class="w-4 h-4 rounded inline-block border border-gray-300" style="background-color: {{ $category->color }};"></div></span>
                        </div>
                    </div>

                    <!-- Category Stats -->
                    <div class="p-4 bg-gray-50">
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ $category->products_count }}</p>
                                <p class="text-xs text-gray-600">Total Products</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ $category->active_products_count }}</p>
                                <p class="text-xs text-gray-600">Active Products</p>
                            </div>
                        </div>
                    </div>

                    <!-- Category Actions -->
                    <div class="p-4 border-t border-gray-200 bg-white">
                        <div class="flex justify-between items-center">
                            <a href="{{ route('categories.show', $category) }}"
                               class="text-primary-600 hover:text-primary-700 font-medium text-sm flex items-center">
                                <i class="lni lni-eye mr-1"></i> View Products
                            </a>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('categories.edit', $category) }}"
                                   class="text-gray-600 hover:text-gray-700 p-1 rounded hover:bg-gray-100">
                                    <i class="lni lni-pencil"></i>
                                </a>
                                <form action="{{ route('categories.destroy', $category) }}"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-700 p-1 rounded hover:bg-red-50
                                           {{ $category->products_count > 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ $category->products_count > 0 ? 'disabled' : '' }}>
                                        <i class="lni lni-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <x-ui.card class="text-center py-12">
                        <i class="lni lni-tag text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Categories Found</h3>
                        <p class="text-gray-600 mb-4">Get started by creating your first product category.</p>
                        <a href="{{ route('categories.create') }}"
                           class="bg-primary-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-700 transition-colors inline-flex items-center">
                            <i class="lni lni-plus mr-2"></i>
                            Create Category
                        </a>
                    </x-ui.card>
                </div>
            @endforelse
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $categories->count() }}</p>
                <p class="text-sm text-gray-600">Total Categories</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $categories->where('is_active', true)->count() }}</p>
                <p class="text-sm text-gray-600">Active Categories</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $categories->sum('products_count') }}</p>
                <p class="text-sm text-gray-600">Total Products</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $categories->sum('active_products_count') }}</p>
                <p class="text-sm text-gray-600">Active Products</p>
            </x-ui.card>
        </div>
    </div>
@endsection
