@extends('settings.index')

@section('settings-content')
    <div class="space-y-6">
        <h2 class="text-2xl font-bold text-gray-900">Roles & Permissions</h2>

        <!-- Roles Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($roles as $role)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $role->display_name }}</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $role->name === 'admin' ? 'bg-purple-100 text-purple-800' :
                           ($role->name === 'manager' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                        {{ $role->users_count }} users
                    </span>
                        </div>

                        @if($role->description)
                            <p class="text-sm text-gray-600 mb-4">{{ $role->description }}</p>
                        @endif

                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>{{ $role->name }}</span>
                            @if($role->is_system)
                                <span class="text-xs text-gray-400">System Role</span>
                            @endif
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="p-4 bg-gray-50">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Permissions</h4>
                        <div class="space-y-2">
                            @foreach($permissions as $module => $actions)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 capitalize">{{ $module }}</span>
                                    <div class="flex space-x-1">
                                        @foreach($actions as $action)
                                            @php
                                                $permissionString = $module . '.' . $action;
                                                $hasPermission = $role->hasPermission($permissionString) || $role->hasPermission('*');
                                            @endphp
                                            <span class="text-xs px-2 py-1 rounded
                                {{ $hasPermission ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-400' }}">
                                {{ $action }}
                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    @if(!$role->is_system)
                        <div class="p-4 border-t border-gray-200 bg-white">
                            <div class="flex justify-between items-center">
                                <button onclick="openPermissionsModal({{ $role->id }})"
                                        class="text-primary-600 hover:text-primary-700 font-medium text-sm flex items-center">
                                    <i class="lni lni-shield mr-1"></i> Manage Permissions
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Permissions Modal -->
    <div id="permissionsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl mx-4">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Manage Permissions</h3>
            </div>

            <form id="permissionsForm" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 max-h-96 overflow-y-auto">
                    <div class="space-y-6">
                        @foreach($permissions as $module => $actions)
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-3 capitalize">{{ $module }}</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @foreach($actions as $action)
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]"
                                                   value="{{ $module }}.{{ $action }}"
                                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                            <span class="ml-2 text-sm text-gray-700 capitalize">{{ $action }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closePermissionsModal()"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Cancel
                    </button>
                    <x-ui.button variant="primary" type="submit">
                        Save Permissions
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openPermissionsModal(roleId) {
            // Set form action
            document.getElementById('permissionsForm').action = `/settings/roles/${roleId}/permissions`;

            // Load current permissions and check checkboxes
            // This would typically involve an API call to get current permissions
            // For now, we'll just show the modal
            document.getElementById('permissionsModal').classList.remove('hidden');
        }

        function closePermissionsModal() {
            document.getElementById('permissionsModal').classList.add('hidden');
        }
    </script>
@endpush
