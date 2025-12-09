@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Return Order</h1>
                <p class="text-gray-600 mt-1">Search a sale to begin the return process</p>
            </div>
        </div>

        <x-ui.card class="p-6">
            <form method="GET" action="{{ route('returns.results') }}">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="text-gray-700 font-medium">Invoice Number</label>
                        <input type="text" name="invoice_number"
                               value="{{ request('invoice_number') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               placeholder="INV-20250101-0001">
                    </div>

                    <div class="flex items-end">
                        <button class="bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 w-full">
                            Search
                        </button>
                    </div>
                </div>
            </form>

            @isset($sale)
                <div class="mt-6">
                    <a href="{{ route('returns.create', $sale) }}"
                       class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700">
                        Proceed to Return Items
                    </a>
                </div>
            @endisset
        </x-ui.card>
    </div>
@endsection
