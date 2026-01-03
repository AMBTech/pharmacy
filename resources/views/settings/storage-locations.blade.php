@extends('settings.index')

@section('settings-content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Storage Locations</h2>

            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                <!-- Add Storage Location Button -->
                <button onclick="openAddLocationModal()"
                        class="bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors flex items-center">
                    <i class="lni lni-plus mr-2"></i>
                    Add Storage Location
                </button>
            @endif
        </div>

        <!-- Storage Locations Table -->
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="w-full" id="storageLocationsTable">
                    <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">ID</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Bucket</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Shelf</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Slot</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Label</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Status</th>
                        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Actions</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($storageLocations as $location)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-4 text-gray-900">{{ $location->id }}</td>
                            <td class="py-4 px-4 text-gray-900 font-medium">{{ $location->bucket_code }}</td>
                            <td class="py-4 px-4 text-gray-900 font-medium">{{ $location->shelf_code }}</td>
                            <td class="py-4 px-4 text-gray-900 font-medium">{{ $location->slot_code ?? '-' }}</td>
                            <td class="py-4 px-4 text-gray-600">
                                {{ $location->label ?? '-' }}
                                @if($location->description)
                                    <span class="text-xs text-gray-500 block">{{ $location->description }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $location->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $location->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                                <td class="py-4 px-4">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="openEditLocationModal({{ $location->id }}, '{{ $location->bucket_code }}', '{{ $location->shelf_code }}', '{{ $location->slot_code }}', '{{ $location->label }}', '{{ $location->description }}', {{ $location->is_active ? 'true' : 'false' }})"
                                                class="text-gray-600 hover:text-gray-700 p-2 rounded hover:bg-gray-100">
                                            <i class="lni lni-pencil"></i>
                                        </button>
                                        <form action="{{ route('settings.storage-locations.destroy', $location) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('Are you sure you want to delete this storage location?')"
                                                    class="text-red-600 hover:text-red-700 p-2 rounded hover:bg-red-50">
                                                <i class="lni lni-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    @if(auth()->user()->isAdmin() || auth()->user()->isManager())
        <!-- Add/Edit Storage Location Modal -->
        <div id="locationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">Add Storage Location</h3>
                </div>

                <form id="locationForm" method="POST">
                    @csrf
                    <div id="formMethod" style="display: none;"></div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bucket Code *</label>
                            <input type="text"
                                   name="bucket_code"
                                   id="bucket_code"
                                   required
                                   maxlength="20"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="e.g. B01">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Shelf Code *</label>
                            <input type="text"
                                   name="shelf_code"
                                   id="shelf_code"
                                   required
                                   maxlength="20"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="e.g. S2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Slot Code</label>
                            <input type="text"
                                   name="slot_code"
                                   id="slot_code"
                                   maxlength="20"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="e.g. P4">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Label</label>
                            <input type="text"
                                   name="label"
                                   id="label"
                                   maxlength="255"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="e.g. Hearing Aids – Small">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description"
                                      id="description"
                                      rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                      placeholder="Optional description"></textarea>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_active"
                                   id="is_active"
                                   value="1"
                                   checked
                                   class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                            <label for="is_active" class="ml-2 text-sm text-gray-700">
                                Location is active
                            </label>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                        <button type="button" onclick="closeLocationModal()"
                                class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                            Cancel
                        </button>
                        <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                            <span id="submitButtonText">Create Location</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        let currentEditingLocationId = null;

        function openAddLocationModal() {
            currentEditingLocationId = null;
            document.getElementById('modalTitle').textContent = 'Add Storage Location';
            document.getElementById('submitButtonText').textContent = 'Create Location';
            document.getElementById('locationForm').action = "{{ route('settings.storage-locations.store') }}";
            document.getElementById('formMethod').innerHTML = '';

            // Reset form
            document.getElementById('locationForm').reset();
            document.getElementById('is_active').checked = true;

            document.getElementById('locationModal').classList.remove('hidden');
        }

        function openEditLocationModal(locationId, bucketCode, shelfCode, slotCode, label, description, isActive) {
            currentEditingLocationId = locationId;
            document.getElementById('modalTitle').textContent = 'Edit Storage Location';
            document.getElementById('submitButtonText').textContent = 'Update Location';
            document.getElementById('locationForm').action = `/settings/storage-locations/${locationId}`;
            document.getElementById('formMethod').innerHTML = '@method('PUT')';

            // Fill form with location data
            document.getElementById('bucket_code').value = bucketCode;
            document.getElementById('shelf_code').value = shelfCode;
            document.getElementById('slot_code').value = slotCode || '';
            document.getElementById('label').value = label || '';
            document.getElementById('description').value = description || '';
            document.getElementById('is_active').checked = isActive;

            document.getElementById('locationModal').classList.remove('hidden');
        }

        function closeLocationModal() {
            document.getElementById('locationModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('locationModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeLocationModal();
            }
        });

        // Initialize DataTable
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof $ !== 'undefined' && $.fn.DataTable) {
                $('#storageLocationsTable').DataTable({
                    pageLength: 25,
                    order: [[1, 'asc'], [2, 'asc'], [3, 'asc']],
                    columnDefs: [
                        { orderable: false, targets: -1 } // Disable sorting on Actions column
                    ]
                });
            }
        });
    </script>
@endpush
