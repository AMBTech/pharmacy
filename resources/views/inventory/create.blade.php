@extends('layouts.app')

@section('content')
    <div class="max-w-full mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Add New Product</h1>
                    <p class="text-gray-600 mt-1">Add a new product to your inventory</p>
                </div>
                <a href="{{ route('inventory.index') }}"
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Inventory
                </a>
            </div>

            <!-- Product Form -->
            <form action="{{ route('inventory.store') }}" method="POST">
                @csrf

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
                                   value="{{ old('name') }}"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="generic_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Generic Name
                            </label>
                            <input type="text"
                                   name="generic_name"
                                   id="generic_name"
                                   value="{{ old('generic_name') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">
                                Brand
                            </label>
                            <input type="text"
                                   name="brand"
                                   id="brand"
                                   value="{{ old('brand') }}"
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
                                   value="{{ old('price') }}"
                                   step="0.01"
                                   min="0"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('price')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
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
                                <option value="Tablet" {{ old('unit') == 'Tablet' ? 'selected' : '' }}>Tablet</option>
                                <option value="Capsule" {{ old('unit') == 'Capsule' ? 'selected' : '' }}>Capsule</option>
                                <option value="Bottle" {{ old('unit') == 'Bottle' ? 'selected' : '' }}>Bottle</option>
                                <option value="Tube" {{ old('unit') == 'Tube' ? 'selected' : '' }}>Tube</option>
                                <option value="Box" {{ old('unit') == 'Box' ? 'selected' : '' }}>Box</option>
                                <option value="Strip" {{ old('unit') == 'Strip' ? 'selected' : '' }}>Strip</option>
                                <option value="Injection" {{ old('unit') == 'Injection' ? 'selected' : '' }}>Injection</option>
                                <option value="Syrup" {{ old('unit') == 'Syrup' ? 'selected' : '' }}>Syrup</option>
                                <option value="Other" {{ old('unit') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('unit')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
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
                           value="{{ old('barcode') }}"
                           step="0.01"
                           min="0"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('barcode')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
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
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               id="is_active"
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
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
                        Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
