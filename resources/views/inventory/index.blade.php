@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Inventory Management</h1>
                <p class="text-gray-600 mt-1">Manage products, stock levels, and batches</p>
            </div>
            @hasPermission('inventory.create')
                <a href="{{ route('inventory.create') }}"
                   class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors flex items-center">
                    <i class="lni lni-plus mr-2"></i>
                    Add New Product
                </a>
            @endhasPermission
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('inventory.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Products</label>
                    <input type="text"
                           name="search"
                           id="search"
                           value="{{ request('search') }}"
                           placeholder="Name, generic, brand..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category"
                            id="category"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->name }}" {{ request('category') == $category->name ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Stock Status Filter -->
                <div>
                    <label for="stock_status" class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
                    <select name="stock_status"
                            id="stock_status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Stock</option>
                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock (< 10)
                        </option>
                        <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock
                        </option>
                        <option value="sufficient" {{ request('stock_status') == 'sufficient' ? 'selected' : '' }}>
                            Sufficient Stock
                        </option>
                    </select>
                </div>

                <!-- Filter Actions -->
                <div class="flex items-end space-x-2">
                    <button type="submit"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors flex-1">
                        <i class="lni lni-search-alt mr-2"></i>Filter
                    </button>
                    <a href="{{ route('inventory.index') }}"
                       class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors flex items-center">
                        <i class="lni lni-reload mr-1"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Inventory Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="lni lni-package text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Products</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $products->total() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mr-4">
{{--                        <i class="lni lni-exclamation text-orange-600 text-xl"></i>--}}
                        <x-ui.icon name="stock"></x-ui.icon>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Low Stock Items</p>
                        <p class="text-3xl font-bold text-gray-900">
                            {{ \App\Models\Product::where('stock', '<', 10)->where('stock', '>', 0)->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
{{--                        <i class="lni lni-xmark text-red-600 text-xl"></i>--}}
                        <x-ui.icon name="xmark"></x-ui.icon>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Out of Stock</p>
                        <p class="text-3xl font-bold text-gray-900">
                            {{ \App\Models\Product::where('stock', 0)->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <x-ui.card title="Inventory List" padding="p-6">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Barcode
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Stock
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="lni lni-package text-gray-400"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $product->generic_name }}</div>
                                            <div class="text-xs text-gray-400">{{ $product->brand }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $product->barcode }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">

                                    <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $product->category_name ?? "N/A" }}
                                </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $product->stock }} {{ $product->unit }}</div>
                                    <div class="text-xs text-gray-500">{{ count($product->activeBatches) }} batches</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $currency_symbol }} {{ number_format($product->price, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->stock == 0)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Out of Stock
                                    </span>
                                    @elseif($product->stock < 10)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        Low Stock
                                    </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        In Stock
                                    </span>
                                    @endif

                                    @if(!$product->is_active)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 ml-1">
                                        Inactive
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
    {{--                                    <button onclick="openBatchModal({{ $product->id }})"--}}
    {{--                                            class="text-blue-600 hover:text-blue-900 flex items-center text-sm">--}}
    {{--                                        <i class="lni lni-eye mr-1"></i> Batches--}}
    {{--                                    </button>--}}
    {{--                                    @php $product = $product->id; @endphp--}}
                                        <a href="{{ route('inventory.batches.manage', compact('product')) }}"
                                                class="text-blue-600 hover:text-blue-900 flex items-center text-sm">
                                            <i class="lni lni-eye mr-1"></i> Batches
                                        </a>
                                        @hasPermission('inventory.edit')
                                            <a href="{{ route('inventory.edit', $product) }}"
                                               class="text-green-600 hover:text-green-900 flex items-center text-sm">
                                                <i class="lni lni-pencil mr-1"></i> Edit
                                            </a>
                                        @endhasPermission
                                        @hasPermission('inventory.delete')
                                            <form action="{{ route('inventory.destroy', $product) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this product?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-600 hover:text-red-900 flex items-center text-sm">
                                                    <i class="lni lni-trash-can mr-1"></i> Delete
                                                </button>
                                            </form>
                                        @endhasPermission
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    <i class="lni lni-package text-4xl text-gray-300 mb-2 block"></i>
                                    No products found.
                                    <a href="{{ route('inventory.create') }}"
                                       class="text-blue-600 hover:text-blue-800 ml-1">
                                        Add your first product
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="bg-white px-6 py-3 border-t border-gray-200">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </x-ui.card>
    </div>

    <!-- Batch Management Modal -->
    <div id="batchModal" class="fixed inset-0 bg-gray-400 bg-opacity-70 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Batch Management</h3>
                <button onclick="closeBatchModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="lni lni-close text-2xl"></i>
                </button>
            </div>

            <div id="batchModalContent">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openBatchModal(productId) {
            fetch(`/inventory/${productId}/batches`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('batchModalContent').innerHTML = html;
                    document.getElementById('batchModal').classList.remove('hidden');
                    // document.body.style.overflow = 'hidden';
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('batchModalContent').innerHTML = `
                        <div class="text-center py-8">
                            <i class="lni lni-exclamation-circle text-4xl text-red-300 mb-2"></i>
                            <p class="text-red-500">Error loading batch data.</p>
                        </div>
                    `;
                });
        }

        function closeBatchModal() {
            document.getElementById('batchModal').classList.add('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Close modal when clicking outside
        document.getElementById('batchModal').addEventListener('click', function (e) {
            if (e.target.id === 'batchModal') {
                closeBatchModal();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeBatchModal();
            }
        });

        // Handle form submission within modal
        document.addEventListener('submit', function (e) {
            if (e.target.closest('#batchModalContent form')) {
                e.preventDefault();

                const form = e.target;
                const formData = new FormData(form);

                fetch(form.action, {
                    method: form.method,
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the modal content to show updated batches
                            const productId = form.action.split('/').filter(Boolean).pop();
                            openBatchModal(productId);

                            // Show success message
                            showNotification(data.message || 'Batch added successfully!', 'success');
                        } else {
                            showNotification(data.message || 'Error adding batch!', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error adding batch!', 'error');
                    });
            }
        });

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' :
                    type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
@endpush
