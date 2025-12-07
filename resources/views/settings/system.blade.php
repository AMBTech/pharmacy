@extends('settings.index')

@section('settings-content')
    <div class="max-w-4xl">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">System Settings</h2>

        <form action="{{ route('settings.system.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Business Information -->
                <x-ui.card title="Business Information">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input
                            label="Company Name"
                            name="company_name"

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            value="{{ old('company_name', $settings->company_name) }}"
                            placeholder="Enter company name"
                        />

                        <input
                            label="Company Email"
                            name="company_email"

                            type="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            value="{{ old('company_email', $settings->company_email) }}"
                            placeholder="Enter company email"
                        />

                        <input
                            label="Company Phone"
                            name="company_phone"

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            value="{{ old('company_phone', $settings->company_phone) }}"
                            placeholder="Enter company phone"
                        />

                        <input
                            label="License Number"
                            name="license_number"

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            value="{{ old('company_phone', $settings->license_number) }}"
                            placeholder="Enter license number"
                        />

                    </div>
                    <div class="w-full mt-6">
                        <textarea
                            label="Company Address"
                            name="company_address"
                            placeholder="Enter company address"
                            rows="3"

                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        >{{ old('company_address', $settings->company_address) }}</textarea>
                    </div>
                </x-ui.card>

                <!-- System Configuration -->
                <x-ui.card title="System Configuration">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Tax Rate (%)
                            </label>
                        <input
                            placeholder="Tax Rate (%)"
                            name="tax_rate"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            value="{{ old('tax_rate', $settings->tax_rate) }}"
                            required
                        />
                            @error('tax_rate')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Currency
                            </label>
                            <select name="currency" id="currency"

                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="PKR" {{old('currency', $settings->currency) == 'PKR' ? 'selected' : ''}}>PKR</option>
                                <option value="USD" {{old('currency', $settings->currency) == 'USD' ? 'selected' : ''}}>USD</option>
                                <option value="EUR" {{old('currency', $settings->currency) == 'EUR' ? 'selected' : ''}}>Euro</option>
                                <option value="GBP" {{old('currency', $settings->currency) == 'GBP' ? 'selected' : ''}}>GBP</option>
                            </select>
                            {{--<input
                                label="Currency"
                                name="currency"
                                value="{{ old('currency', $settings->currency) }}"
                                placeholder="e.g. USD, EUR"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                required
                            />--}}
                            @error('currency')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Low Stock Threshold
                            </label>
                        <input
                            placeholder="Low Stock Threshold"
                            name="low_stock_threshold"
                            type="number"
                            min="1"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            value="{{ old('low_stock_threshold', $settings->low_stock_threshold) }}"
                            required
                        />
                            @error('low_stock_threshold')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </x-ui.card>

                <!-- Actions -->
                <div class="flex justify-end space-x-4">
                    <x-ui.button variant="secondary" type="button">
                        Cancel
                    </x-ui.button>
                    <x-ui.button variant="primary" type="submit">
                        <i class="lni lni-save mr-2"></i>
                        Save Changes
                    </x-ui.button>
                </div>
            </div>
        </form>
    </div>
@endsection
