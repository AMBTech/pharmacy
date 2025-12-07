@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: {{ $category->color }};">
                    <i class="lni lni-tag text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $category->name }}</h1>
                    <p class="text-gray-600 mt-1">{{ $category->description ?: 'No description provided' }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('categories.index') }}"
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    All Categories
                </a>
                @hasPermission('categories.edit')
                    <a href="{{ route('categories.edit', $category) }}"
                       class="bg-primary-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors flex items-center">
                        <i class="lni lni-pencil mr-2"></i>
                        Edit Category
                    </a>
                @endhasPermission
            </div>
        </div>

        <!-- Category Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="lni lni-package text-blue-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $category->products_count }}</p>
                <p class="text-sm text-gray-600">Total Products</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="lni lni-checkmark-circle text-green-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $category->active_products_count }}</p>
                <p class="text-sm text-gray-600">Active Products</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="lni lni-layers text-orange-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $category->products_sum_stock ?? 0 }}</p>
                <p class="text-sm text-gray-600">Total Stock</p>
            </x-ui.card>

            <x-ui.card class="text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="lni lni-stats-up text-purple-600"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">
                    {{ $category->products_count > 0 ? round(($category->active_products_count / $category->products_count) * 100) : 0 }}%
                </p>
                <p class="text-sm text-gray-600">Active Rate</p>
            </x-ui.card>
        </div>

        <!-- Category Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Category Information -->
            <x-ui.card title="Category Information" padding="p-6">
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Status</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Sort Order</span>
                        <span class="text-sm text-gray-900">{{ $category->sort_order }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Color</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 rounded border border-gray-300" style="background-color: {{ $category->color }};"></div>
                            <span class="text-sm text-gray-900">{{ $category->color }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Created</span>
                        <span class="text-sm text-gray-900">{{ $category->created_at->format('M j, Y') }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm font-medium text-gray-600">Last Updated</span>
                        <span class="text-sm text-gray-900">{{ $category->updated_at->format('M j, Y') }}</span>
                    </div>
                </div>
            </x-ui.card>

            <!-- Stock Status -->
            <x-ui.card title="Stock Overview" padding="p-6">
                <div class="space-y-4">
                    @php
                        $lowStock = $products->where('stock', '<', 10)->where('stock', '>', 0)->count();
                        $outOfStock = $products->where('stock', 0)->count();
                        $sufficientStock = $products->where('stock', '>=', 10)->count();
                    @endphp

                    <div class="flex items-center justify-between p-3 rounded-lg bg-green-50 border border-green-200">
                        <div class="flex items-center">
                            <i class="lni lni-checkmark-circle text-green-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-green-900">Sufficient Stock</p>
                                <p class="text-sm text-green-600">≥ 10 units</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-green-900">{{ $sufficientStock }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-lg bg-orange-50 border border-orange-200">
                        <div class="flex items-center">
                            <i class="lni lni-exclamation-circle text-orange-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-orange-900">Low Stock</p>
                                <p class="text-sm text-orange-600">1-9 units</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-orange-900">{{ $lowStock }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-lg bg-red-50 border border-red-200">
                        <div class="flex items-center">
                            <i class="lni lni-times-circle text-red-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-red-900">Out of Stock</p>
                                <p class="text-sm text-red-600">0 units</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-red-900">{{ $outOfStock }}</span>
                    </div>
                </div>
            </x-ui.card>

            <!-- Quick Actions -->
            <x-ui.card title="Quick Actions" padding="p-6">
                <div class="space-y-3">
                    <a href="{{ route('inventory.create') }}?category_id={{ $category->id }}"
                       class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-primary-300 hover:bg-primary-50 transition-colors group">
                        <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="lni lni-plus text-primary-600"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 group-hover:text-primary-600">Add Product</h3>
                            <p class="text-sm text-gray-600">Create new product in this category</p>
                        </div>
                    </a>

                    <a href="{{ route('inventory.index') }}?category={{ $category->name }}"
                       class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-green-300 hover:bg-green-50 transition-colors group">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="lni lni-package text-green-600"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 group-hover:text-green-600">View All Products</h3>
                            <p class="text-sm text-gray-600">Browse all products in this category</p>
                        </div>
                    </a>

                    @if($category->products_count > 0)
                        <a href="{{ route('reports.sales-by-category') }}?category={{ $category->name }}"
                           class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-colors group">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="lni lni-stats-up text-purple-600"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 group-hover:text-purple-600">Sales Report</h3>
                                <p class="text-sm text-gray-600">View sales analytics for this category</p>
                            </div>
                        </a>
                    @endif
                </div>
            </x-ui.card>
        </div>

        <!-- Products Table -->
        <x-ui.card title="Products in this Category" padding="p-6">
            <!-- Products Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Products ({{ $products->total() }})</h3>
                    <p class="text-gray-600 mt-1">All products belonging to this category</p>
                </div>
                @hasPermission('inventory.create')
                    <a href="{{ route('inventory.create') }}?category_id={{ $category->id }}"
                       class="bg-primary-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors flex items-center">
                        <i class="lni lni-plus mr-2"></i>
                        Add Product
                    </a>
                @endhasPermission
            </div>

            @if($products->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Brand & Generic
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Stock
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($products as $product)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="lni lni-package text-gray-400"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $product->unit }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $product->brand ?: 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $product->generic_name ?: 'No generic name' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $product->stock }}</div>
                                    <div class="text-xs text-gray-500">{{ $product->batches_count }} batches</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rs. {{ number_format($product->price, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->stock == 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Out of Stock
                                </span>
                                    @elseif($product->stock < 10)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    Low Stock
                                </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    In Stock
                                </span>
                                    @endif

                                    @if(!$product->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 ml-1">
                                    Inactive
                                </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        @hasPermission('inventory.edit')
                                            <a href="{{ route('inventory.edit', $product) }}"
                                               class="text-primary-600 hover:text-primary-900 flex items-center text-sm">
                                                <i class="lni lni-pencil mr-1"></i> Edit
                                            </a>
                                        @endhasPermission
                                        @hasPermission('batches.view')
                                            <a href="{{route('inventory.batches.manage', compact('product'))}}"
                                                    class="text-blue-600 hover:text-blue-900 flex items-center text-sm">
                                                <i class="lni lni-eye mr-1"></i> Batches
                                            </a>
                                        @endhasPermission
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="bg-white px-6 py-4 border-t border-gray-200 mt-4">
                        {{ $products->links() }}
                    </div>
                @endif

            @else
                <div class="text-center py-12">
                    <i class="lni lni-package text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Products Found</h3>
                    <p class="text-gray-600 mb-4">There are no products in this category yet.</p>
                    <a href="{{ route('inventory.create') }}?category_id={{ $category->id }}"
                       class="bg-primary-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-700 transition-colors inline-flex items-center">
                        <i class="lni lni-plus mr-2"></i>
                        Add First Product
                    </a>
                </div>
            @endif
        </x-ui.card>
    </div>

@endsection
