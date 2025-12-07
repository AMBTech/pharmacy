@extends('settings.index')

@section('settings-content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Roles & Permissions</h2>
            @canPermission('roles.create')
            <button onclick="openCreateRoleModal()"
                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors flex items-center">
                <i class="lni lni-plus mr-2"></i> Create New Role
            </button>
            @endcanPermission
        </div>

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
                            @foreach($permissions as $module => $data)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">{{ $data['label'] }}</span>
                                    <div class="flex space-x-1">
                                        @foreach($data['permissions'] as $perm)
                                            @php
                                                $hasPermission = $role->hasPermission($perm['key']) || $role->hasPermission('*');
                                            @endphp
                                            <span class="text-xs px-2 py-1 rounded
                                                {{ $hasPermission ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-400' }}">
                                                {{ $perm['action'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="p-4 border-t border-gray-200 bg-white">
                        <div class="flex justify-between items-center">
                            @canPermission('roles.edit')
                            <button onclick="openPermissionsModal({{ $role->id }})"
                                    class="text-primary-600 hover:text-primary-700 font-medium text-sm flex items-center">
                                <i class="lni lni-shield mr-1"></i> Manage Permissions
                            </button>
                            @endcanPermission

                            @if(!$role->is_system)
                                <div class="flex space-x-2">
                                    @canPermission('roles.edit')
                                    <button
                                        onclick="openEditRoleModal({{ $role->id }}, '{{ $role->name }}', '{{ $role->display_name }}', '{{ addslashes($role->description ?? '') }}')"
                                        class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center">
                                        <i class="lni lni-pencil mr-1"></i> Edit
                                    </button>
                                    @endcanPermission

                                    @canPermission('roles.delete')
                                    <button onclick="deleteRole({{ $role->id }}, '{{ $role->display_name }}')"
                                            class="text-red-600 hover:text-red-700 font-medium text-sm flex items-center">
                                        <i class="lni lni-trash mr-1"></i> Delete
                                    </button>
                                    @endcanPermission
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Create Role Modal -->
    <div id="createRoleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 sticky top-0 bg-white">
                <h3 class="text-xl font-semibold text-gray-900">Create New Role</h3>
            </div>

            <form id="createRoleForm" method="POST" action="{{ route('settings.roles.store') }}">
                @csrf
                <div class="p-6 space-y-6">
                    <!-- Role Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="create_name" class="block text-sm font-medium text-gray-700 mb-2">Role Name
                                (slug)</label>
                            <input type="text" id="create_name" name="name" required
                                   placeholder="e.g., store_manager"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <p class="text-xs text-gray-500 mt-1">Lowercase letters, numbers, and underscores only</p>
                        </div>
                        <div>
                            <label for="create_display_name" class="block text-sm font-medium text-gray-700 mb-2">Display
                                Name</label>
                            <input type="text" id="create_display_name" name="display_name" required
                                   placeholder="e.g., Store Manager"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                    <div>
                        <label for="create_description"
                               class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="create_description" name="description" rows="2"
                                  placeholder="Brief description of this role..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                    </div>

                    <!-- Permissions Selection -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Assign Permissions</h4>
                        <div class="space-y-6">
                            @foreach($permissions as $module => $data)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <h5 class="text-base font-semibold text-gray-900">{{ $data['label'] }}</h5>
                                            <p class="text-xs text-gray-500">{{ $data['description'] }}</p>
                                        </div>
                                        <label class="flex items-center text-sm text-gray-600">
                                            <input type="checkbox"
                                                   class="module-select-all rounded border-gray-300 text-primary-600 focus:ring-primary-500 mr-2"
                                                   data-module="{{ $module }}"
                                                   onchange="toggleModulePermissions(this, '{{ $module }}', 'create')">
                                            Select All
                                        </label>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        @foreach($data['permissions'] as $perm)
                                            <label class="flex items-center">
                                                <input type="checkbox" name="permissions[]" value="{{ $perm['key'] }}"
                                                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 module-permission-{{ $module }}-create">
                                                <span class="ml-2 text-sm text-gray-700">{{ $perm['label'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 flex justify-end space-x-3 sticky bottom-0 bg-white">
                    <button type="button" onclick="closeCreateRoleModal()"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div id="editRoleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl mx-4">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Edit Role</h3>
            </div>

            <form id="editRoleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">Role
                                Name</label>
                            <input type="text" id="edit_name" name="name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label for="edit_display_name" class="block text-sm font-medium text-gray-700 mb-2">Display
                                Name</label>
                            <input type="text" id="edit_display_name" name="display_name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                    <div>
                        <label for="edit_description"
                               class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="edit_description" name="description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeEditRoleModal()"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Permissions Modal -->
    <div id="permissionsModal"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl mx-4">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Manage Permissions</h3>
            </div>

            <form id="permissionsForm" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 max-h-96 overflow-y-auto">
                    <div class="space-y-6">
                        @foreach($permissions as $module => $data)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h5 class="text-base font-semibold text-gray-900">{{ $data['label'] }}</h5>
                                        <p class="text-xs text-gray-500">{{ $data['description'] }}</p>
                                    </div>
                                    <label class="flex items-center text-sm text-gray-600">
                                        <input type="checkbox"
                                               class="module-select-all rounded border-gray-300 text-primary-600 focus:ring-primary-500 mr-2"
                                               data-module="{{ $module }}"
                                               onchange="toggleModulePermissions(this, '{{ $module }}', 'permissions')">
                                        Select All
                                    </label>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @foreach($data['permissions'] as $perm)
                                        <label class="flex items-center">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm['key'] }}"
                                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 module-permission-{{ $module }}-permissions permission-checkbox">
                                            <span class="ml-2 text-sm text-gray-700">{{ $perm['label'] }}</span>
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
        function openCreateRoleModal() {
            document.getElementById('createRoleModal').classList.remove('hidden');
        }

        function closeCreateRoleModal() {
            document.getElementById('createRoleModal').classList.add('hidden');
            document.getElementById('createRoleForm').reset();
        }

        function openEditRoleModal(roleId, name, displayName, description) {
            document.getElementById('editRoleForm').action = `/settings/roles/${roleId}`;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_display_name').value = displayName;
            document.getElementById('edit_description').value = description;
            document.getElementById('editRoleModal').classList.remove('hidden');
        }

        function closeEditRoleModal() {
            document.getElementById('editRoleModal').classList.add('hidden');
        }

        function openPermissionsModal(roleId) {
            // Set form action
            document.getElementById('permissionsForm').action = `/settings/roles/${roleId}/permissions`;

            // Load current permissions
            fetch(`/settings/roles/${roleId}/permissions`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Loaded permissions:', data.permissions);
                    
                    // Uncheck all checkboxes first
                    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);

                    // Check the permissions this role has
                    if (data.permissions && Array.isArray(data.permissions)) {
                        // If wildcard permission exists, check all
                        if (data.permissions.includes('*')) {
                            document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
                        } else {
                            // Check specific permissions
                            data.permissions.forEach(permission => {
                                const checkbox = document.querySelector(`input.permission-checkbox[value="${permission}"]`);
                                if (checkbox) {
                                    checkbox.checked = true;
                                } else {
                                    console.warn(`Checkbox not found for permission: ${permission}`);
                                }
                            });
                        }
                    }

                    // Show the modal
                    document.getElementById('permissionsModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error loading permissions:', error);
                    alert('Error loading permissions: ' + error.message);
                });
        }

        function closePermissionsModal() {
            document.getElementById('permissionsModal').classList.add('hidden');
        }

        function deleteRole(roleId, roleName) {
            if (confirm(`Are you sure you want to delete the role "${roleName}"?`)) {
                fetch(`/settings/roles/${roleId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || 'Error deleting role');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting role');
                    });
            }
        }

        function toggleModulePermissions(checkbox, module, context) {
            const checkboxes = document.querySelectorAll(`.module-permission-${module}-${context}`);
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
        }
    </script>
@endpush
