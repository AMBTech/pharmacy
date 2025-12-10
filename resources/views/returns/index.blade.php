@extends('layouts.app')

@section('content')
    <div class="space-y-6">

        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Return Orders</h1>
                <p class="text-gray-600 mt-1">All processed returns</p>
            </div>
            <a href="{{ route('returns.search') }}"
               class="bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700">
                New Return
            </a>
        </div>

        <x-ui.card title="Sales" padding="p-6">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Invoice #
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order #
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order Amount
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($returns as $return)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span>{{$return->return_number ?? "N/A"}}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span>{{$return->sale->invoice_number ?? "N/A"}}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span>{{number_format($return->refund_amount, 2) ?? "N/A"}}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $return->status_color }}">
                                            <i class="lni {{ $return->status_icon }} mr-1"></i>
                                            {{ ucfirst($return->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap flex flex-row gap-2">
                                        @if ($return->status !== 'approved')
                                            @hasPermission('returns.approve')
                                            <form action="{{ route('returns.approve', $return) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('Are you sure you want to approve this return?')">
                                                @csrf
                                                @method('POST')
                                                <button type="submit"
                                                        class="text-green-600 hover:text-green-900 p-2 hover:bg-green-100 rounded text-lg"
                                                        title="Approve">
                                                    <i class="lni lni-checkmark-circle"></i>
                                                </button>
                                            </form>
                                            @endhasPermission
                                        @endif

                                        @if ($return->status === 'pending')
                                            @hasPermission('returns.reject')
                                                <button onclick="openRejectModal({{ $return->id }})"
                                                        class="text-red-600 hover:text-red-900 flex items-center text-lg font-bold p-2 hover:bg-red-50 rounded-md"
                                                        title="Reject">
                                                    <i class="lni lni-cross-circle"></i>
                                                </button>
                                            @endhasPermission
                                        @endif

                                        @if ($return->status === 'pending' && !auth()->user()->isAdmin())
                                            <a href="{{route('returns.cancel', $return->id)}}" type="button"
                                                    class="text-green-600 hover:text-green-900 p-2 hover:bg-green-100 rounded text-lg"
                                                    title="Cancel">
                                                <i class="lni lni-reload"></i>
                                            </a>
                                        @endif

                                        @hasPermission('returns.view')
                                            <a href="{{ route('returns.show', $return) }}" target="_blank"
                                               class="text-green-600 hover:text-green-900 flex items-center text-lg font-bold p-2 hover:bg-gray-100 rounded-md" title="Show">
                                                <i class="lni lni-eye"></i>
                                            </a>
                                        @endhasPermission
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </x-ui.card>

        <!-- Reject Modal -->
        <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 overflow-y-auto">
            <div class="relative top-20 mx-auto p-5 w-96 bg-white rounded-lg shadow-xl">
                <h3 class="text-lg font-semibold mb-4">Reject Return Request</h3>

                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                    <p class="text-sm text-yellow-800">
                        <i class="lni lni-warning mr-1"></i>
                        This action cannot be undone. Customer will be notified about the rejection.
                    </p>
                </div>

                <form id="rejectForm" method="POST" action="">
                    @csrf
                    @method('POST')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Rejection Reason <span class="text-red-500">*</span>
                        </label>
                        <textarea name="rejection_reason"
                                  rows="4"
                                  required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                  placeholder="Please provide a detailed reason for rejecting this return request..."
                                  maxlength="1000"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Required. Max 1000 characters.</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Internal Notes (Optional)</label>
                        <textarea name="staff_notes"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                  placeholder="Internal notes for staff reference..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRejectModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Pagination -->
        @if($returns->hasPages())
            <div class="bg-white px-6 py-4 border-t border-gray-200">
                {{ $returns->links() }}
            </div>
        @endif

        <div>
            {{ $returns->links() }}
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function openRejectModal(returnId) {
            const form = document.getElementById('rejectForm');
            form.action = `/returns/${returnId}/reject`;
            document.getElementById('rejectModal').classList.remove('hidden');
            // Focus on the reason textarea
            setTimeout(() => {
                form.querySelector('textarea[name="rejection_reason"]').focus();
            }, 100);
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            // Clear form
            const form = document.getElementById('rejectForm');
            form.reset();
        }

        // Prevent closing modal when clicking inside
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
    </script>
@endpush
