@extends('layouts.app')

@section('content')
    <div class="max-w-full mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Product</h1>
                    <p class="text-gray-600 mt-1">{{ $product->name }}</p>
                </div>
                <a href="{{ route('inventory.index') }}"
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Inventory
                </a>
            </div>

            <!-- Product Form -->
            <form action="{{ route('inventory.update', $product) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Basic Information</h3>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Product Name *
                            </label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name', $product->name) }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="generic_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Generic Name
                            </label>
                            <input type="text"
                                   name="generic_name"
                                   id="generic_name"
                                   value="{{ old('generic_name', $product->generic_name) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">
                                Brand
                            </label>
                            <input type="text"
                                   name="brand"
                                   id="brand"
                                   value="{{ old('brand', $product->brand) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Pricing & Details -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Pricing & Details</h3>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Category *
                            </label>
                            <select name="category_id"
                                    id="category_id"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="">Select Category</option>
                                @foreach(\App\Models\Category::active()->ordered()->get() as $category)
                                    <option value="{{ $category->id }}"
                                        {{ (old('category_id') ?? ($product->category_id ?? '')) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                Selling Price (Rs.) *
                            </label>
                            <input type="number"
                                   name="price"
                                   id="price"
                                   value="{{ old('price', $product->price) }}"
                                   step="0.01"
                                   min="0"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700 mb-1">
                                Unit *
                            </label>
                            <select name="unit"
                                    id="unit"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select Unit</option>
                                <option value="Tablet" {{ old('unit', $product->unit) == 'Tablet' ? 'selected' : '' }}>Tablet</option>
                                <option value="Capsule" {{ old('unit', $product->unit) == 'Capsule' ? 'selected' : '' }}>Capsule</option>
                                <option value="Bottle" {{ old('unit', $product->unit) == 'Bottle' ? 'selected' : '' }}>Bottle</option>
                                <option value="Tube" {{ old('unit', $product->unit) == 'Tube' ? 'selected' : '' }}>Tube</option>
                                <option value="Box" {{ old('unit', $product->unit) == 'Box' ? 'selected' : '' }}>Box</option>
                                <option value="Strip" {{ old('unit', $product->unit) == 'Strip' ? 'selected' : '' }}>Strip</option>
                                <option value="Injection" {{ old('unit', $product->unit) == 'Injection' ? 'selected' : '' }}>Injection</option>
                                <option value="Syrup" {{ old('unit', $product->unit) == 'Syrup' ? 'selected' : '' }}>Syrup</option>
                                <option value="Other" {{ old('unit', $product->unit) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                        Barcode
                    </label>
                    <input type="number"
                           name="barcode"
                           id="barcode"
                           value="{{ old('barcode', $product->barcode) }}"
                           step="0.01"
                           min="0"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Description & Status -->
                <div class="mt-6 space-y-4">
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Description
                        </label>
                        <textarea name="description"
                                  id="description"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               id="is_active"
                               value="1"
                               {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="is_active" class="ml-2 text-sm text-gray-700">
                            Product is active and available for sale
                        </label>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('inventory.index') }}"
                       class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors flex items-center">
                        <i class="lni lni-save mr-2"></i>
                        Update Product
                    </button>
                </div>
            </form>

            <!-- Batch Management Section -->
            <div class="mt-12 border-t pt-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Batch Management</h2>
                        <p class="text-gray-600 mt-1">Manage product batches and stock levels</p>
                    </div>
                    <button onclick="openAddBatchModal()"
                            class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-700 transition-colors flex items-center">
                        <i class="lni lni-plus mr-2"></i>
                        Add New Batch
                    </button>
                </div>

                <!-- Current Stock Summary -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-sm text-blue-600 font-medium">Total Stock</p>
                            <p class="text-2xl font-bold text-blue-800">{{ $product->stock }} {{ $product->unit }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-blue-600 font-medium">Active Batches</p>
                            <p class="text-2xl font-bold text-blue-800">{{ $product->active_batches_count }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-blue-600 font-medium">Status</p>
                            <p class="text-lg font-bold
                            {{ $product->stock == 0 ? 'text-red-600' : ($product->stock < 10 ? 'text-orange-600' : 'text-green-600') }}">
                                {{ $product->stock == 0 ? 'Out of Stock' : ($product->stock < 10 ? 'Low Stock' : 'In Stock') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Batches Table -->
                @if($product->batches->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Batch Number
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Manufacturing Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Expiry Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cost Price
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Selling Price
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
                            @foreach($product->batches as $batch)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $batch->batch_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $batch->manufacturing_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $batch->expiry_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $batch->quantity }} {{ $product->unit }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rs. {{ number_format($batch->cost_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rs. {{ number_format($batch->selling_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $daysToExpiry = $batch->getDaysToExpiryAttribute();
                                        @endphp
                                        @if($daysToExpiry < 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Expired
                                    </span>
                                        @elseif($daysToExpiry < 30)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        Expiring Soon
                                    </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <form action="{{ route('inventory.batches.destroy', $batch) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this batch?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="lni lni-trash-can"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                        <i class="lni lni-package text-4xl text-gray-300 mb-2"></i>
                        <p class="text-gray-500">No batches found for this product.</p>
                        <p class="text-sm text-gray-400 mt-1">Add your first batch to track stock.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Add Batch Modal -->
    <div id="addBatchModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Add New Batch</h3>
                <button onclick="closeAddBatchModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="lni lni-close text-2xl"></i>
                </button>
            </div>

            <form action="{{ route('inventory.batches.store', $product) }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-1">
                            Batch Number *
                        </label>
                        <input type="text"
                               name="batch_number"
                               id="batch_number"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="manufacturing_date" class="block text-sm font-medium text-gray-700 mb-1">
                                MFG Date *
                            </label>
                            <input type="date"
                                   name="manufacturing_date"
                                   id="manufacturing_date"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-1">
                                EXP Date *
                            </label>
                            <input type="date"
                                   name="expiry_date"
                                   id="expiry_date"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">
                            Quantity *
                        </label>
                        <input type="number"
                               name="quantity"
                               id="quantity"
                               required
                               min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-1">
                                Cost Price (Rs.) *
                            </label>
                            <input type="number"
                                   name="cost_price"
                                   id="cost_price"
                                   required
                                   step="0.01"
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="selling_price" class="block text-sm font-medium text-gray-700 mb-1">
                                Selling Price (Rs.) *
                            </label>
                            <input type="number"
                                   name="selling_price"
                                   id="selling_price"
                                   required
                                   step="0.01"
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button"
                            onclick="closeAddBatchModal()"
                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                        Add Batch
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openAddBatchModal() {
            document.getElementById('addBatchModal').classList.remove('hidden');
        }

        function closeAddBatchModal() {
            document.getElementById('addBatchModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('addBatchModal').addEventListener('click', function(e) {
            if (e.target.id === 'addBatchModal') {
                closeAddBatchModal();
            }
        });

        // Set today's date as default for manufacturing date
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('manufacturing_date').value = new Date().toISOString().split('T')[0];

            // Set expiry date to 2 years from now by default
            const twoYearsLater = new Date();
            twoYearsLater.setFullYear(twoYearsLater.getFullYear() + 2);
            document.getElementById('expiry_date').value = twoYearsLater.toISOString().split('T')[0];
        });
    </script>
@endpush
