@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto">
        <x-ui.card>
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create New Category</h1>
                    <p class="text-gray-600 mt-1">Add a new product category to organize your inventory</p>
                </div>
                <a href="{{ route('categories.index') }}"
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Categories
                </a>
            </div>

            <!-- Category Form -->
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Category Name *
                                </label>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       value="{{ old('name') }}"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="e.g., Antibiotics, Pain Relief">
                                @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Description
                                </label>
                                <textarea name="description"
                                          id="description"
                                          rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                          placeholder="Brief description of this category">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Appearance -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Appearance</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700 mb-1">
                                    Category Color *
                                </label>
                                <div class="flex items-center space-x-3">
                                    <input type="color"
                                           name="color"
                                           id="color"
                                           value="{{ old('color', '#3b82f6') }}"
                                           required
                                           class="w-12 h-12 border border-gray-300 rounded-lg cursor-pointer">
                                    <input type="text"
                                           value="{{ old('color', '#3b82f6') }}"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                           readonly>
                                </div>
                                @error('color')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">
                                    Sort Order
                                </label>
                                <input type="number"
                                       name="sort_order"
                                       id="sort_order"
                                       value="{{ old('sort_order', 0) }}"
                                       min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               id="is_active"
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <label for="is_active" class="ml-2 text-sm text-gray-700">
                            Category is active and available for use
                        </label>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 flex justify-end space-x-3 border-t pt-6">
                    <a href="{{ route('categories.index') }}"
                       class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-200 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors flex items-center">
                        <i class="lni lni-save mr-2"></i>
                        Create Category
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>

    <script>
        // Update color text input when color picker changes
        document.getElementById('color').addEventListener('input', function(e) {
            document.querySelector('input[type="text"][readonly]').value = e.target.value;
        });
    </script>
@endsection
