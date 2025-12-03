@extends('layouts.app')

@section('content')
    @php
        $product = $product ?? null;
    @endphp

    @if($product)
        <div class="space-y-6 bg-white rounded-lg shadow-md p-6">
            <!-- Product Header -->
            <div class="flex items-center justify-between">
                <a href="{{ route('inventory.index') }}"
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Inventory
                </a>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900">{{ $product->name }}</h4>
                        <p class="text-sm text-gray-600">{{ $product->generic_name }} • {{ $product->brand }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Current Stock</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $product->stock }} {{ $product->unit }}</p>
                    </div>
                </div>
            </div>

            <!-- Add Batch Form -->
            <div class="border border-gray-200 rounded-lg p-6">
                <h5 class="text-lg font-semibold text-gray-900 mb-4">Add New Batch</h5>
                {{-- <form action="{{ route('inventory.batches.store', $product) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4"> --}}
                    <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4" onsubmit="event.preventDefault(); addBatch();">
                    @csrf

                    <div>
                        <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-1">Batch Number *</label>
                        <input type="text" name="batch_number" id="batch_number" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                        <input type="number" name="quantity" id="quantity" required min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="manufacturing_date" class="block text-sm font-medium text-gray-700 mb-1">MFG Date *</label>
                        <input type="date" name="manufacturing_date" id="manufacturing_date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-1">EXP Date *</label>
                        <input type="date" name="expiry_date" id="expiry_date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-1">Cost Price (Rs.) *</label>
                        <input type="number" name="cost_price" id="cost_price" required step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="selling_price" class="block text-sm font-medium text-gray-700 mb-1">Selling Price (Rs.) *</label>
                        <input type="number" name="selling_price" id="selling_price" required step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div class="md:col-span-2 flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeBatchModal()"
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

            <!-- Existing Batches -->
            @if($product->batches->count() > 0)
                <div>
                    <h5 class="text-lg font-semibold text-gray-900 mb-4">Existing Batches</h5>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MFG Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EXP Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selling</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($product->batches as $batch)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $batch->batch_number }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $batch->manufacturing_date->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $batch->expiry_date->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $batch->quantity }} {{ $product->unit }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">Rs. {{ number_format($batch->cost_price, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">Rs. {{ number_format($batch->selling_price, 2) }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $daysToExpiry = $batch->getDaysToExpiryAttribute();
                                        @endphp
                                        @if($daysToExpiry < 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Expired
                                    </span>
                                        @elseif($daysToExpiry < 30)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        Expiring Soon
                                    </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-medium">
                                        <form action="{{ route('inventory.batches.destroy', $batch) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this batch?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="lni lni-trash-can"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                    <i class="lni lni-package text-4xl text-gray-300 mb-2"></i>
                    <p class="text-gray-500">No batches found for this product.</p>
                    <p class="text-sm text-gray-400 mt-1">Add your first batch above.</p>
                </div>
            @endif
        </div>

        <script>
            // Set default dates for the form
            document.addEventListener('DOMContentLoaded', function() {
                const today = new Date().toISOString().split('T')[0];
                const twoYearsLater = new Date();
                twoYearsLater.setFullYear(twoYearsLater.getFullYear() + 2);

                if (document.getElementById('manufacturing_date')) {
                    document.getElementById('manufacturing_date').value = today;
                }
                if (document.getElementById('expiry_date')) {
                    document.getElementById('expiry_date').value = twoYearsLater.toISOString().split('T')[0];
                }
            });

            function addBatch() {
                // get form values
                const batchNumber = document.getElementById('batch_number').value;
                const quantity = document.getElementById('quantity').value;
                const mfgDate = document.getElementById('manufacturing_date').value;
                const expDate = document.getElementById('expiry_date').value;
                const costPrice = document.getElementById('cost_price').value;
                const sellingPrice = document.getElementById('selling_price').value;
                // Perform validation (example)
                if (!batchNumber || !quantity || !mfgDate || !expDate || !costPrice || !sellingPrice) {
                    showNotification('Please fill in all required fields.', 'error');
                    return;
                }
                
                // Submit the form
                document.querySelector('form').submit();
                showNotification('Batch added successfully!', 'success');
            }

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
    @else
        <div class="text-center py-8">
            <i class="lni lni-exclamation-circle text-4xl text-gray-300 mb-2"></i>
            <p class="text-gray-500">Product not found.</p>
        </div>
    @endif
@endsection
