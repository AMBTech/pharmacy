@extends('layouts.app')

@section('content')
    <div class="space-y-6 max-w-2xl mx-auto">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ isset($supplier) ? 'Edit Supplier' : 'Add New Supplier' }}
                </h1>
                <p class="text-gray-600 mt-1">
                    {{ isset($supplier) ? 'Update supplier information' : 'Create a new supplier record' }}
                </p>
            </div>
            <div>
                <a href="{{ route('purchases.suppliers.index') }}"
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors flex items-center">
                    <i class="lni lni-arrow-left mr-2"></i>
                    Back to Suppliers
                </a>
            </div>
        </div>

        <form action="{{ isset($supplier) ? route('purchases.suppliers.update', $supplier) : route('purchases.suppliers.store') }}" method="POST">
            @csrf
            @if(isset($supplier))
                @method('PUT')
            @endif

            <x-ui.card>
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Supplier Name *</label>
                                <input type="text" name="name" required
                                       value="{{ old('name', $supplier->name ?? '') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Enter supplier name">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Person</label>
                                <input type="text" name="contact_person"
                                       value="{{ old('contact_person', $supplier->contact_person ?? '') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Enter contact person name">
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <input type="email" name="email"
                                       value="{{ old('email', $supplier->email ?? '') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Enter email address">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="text" name="phone"
                                       value="{{ old('phone', $supplier->phone ?? '') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Enter phone number">
                            </div>
                        </div>
                    </div>

                    <!-- Address & Tax -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Address & Tax Information</h3>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <textarea name="address" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                          placeholder="Enter supplier address">{{ old('address', $supplier->address ?? '') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tax Number</label>
                                <input type="text" name="tax_number"
                                       value="{{ old('tax_number', $supplier->tax_number ?? '') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Enter tax registration number">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea name="notes" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                          placeholder="Add any notes about this supplier...">{{ old('notes', $supplier->notes ?? '') }}</textarea>
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" value="1"
                                           {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Active Supplier</span>
                                </label>
                                <p class="text-sm text-gray-500 mt-1">
                                    Inactive suppliers won't appear in dropdowns when creating purchase orders.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="window.history.back()"
                                class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                            {{ isset($supplier) ? 'Update Supplier' : 'Create Supplier' }}
                        </button>
                    </div>
                </div>
            </x-ui.card>
        </form>
    </div>
@endsection
