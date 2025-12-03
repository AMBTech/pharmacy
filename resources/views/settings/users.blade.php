@extends('settings.index')

@section('settings-content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">User Management</h2>

            <!-- Add User Button -->
            <button onclick="openAddUserModal()"
                    class="bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors flex items-center">
                <i class="lni lni-plus mr-2"></i>
                Add New User
            </button>
        </div>

        <!-- Users Table -->
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">User</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Role</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Email</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-900">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-white font-semibold text-sm">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-sm text-gray-500">Joined {{ $user->created_at->format('M j, Y') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                {{ $user->role->name === 'admin' ? 'bg-purple-100 text-purple-800' :
                                   ($user->role->name === 'manager' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                                {{ $user->role->display_name }}
                            </span>
                            </td>
                            <td class="py-4 px-4 text-gray-600">{{ $user->email }}</td>
                            <td class="py-4 px-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openEditUserModal({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', {{ $user->role_id }})"
                                            class="text-gray-600 hover:text-gray-700 p-2 rounded hover:bg-gray-100">
                                        <i class="lni lni-pencil"></i>
                                    </button>
                                    <form action="{{ route('settings.users.destroy', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Are you sure you want to delete this user?')"
                                                class="text-red-600 hover:text-red-700 p-2 rounded hover:bg-red-50">
                                            <i class="lni lni-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">Add New User</h3>
            </div>

            <form id="userForm" method="POST">
                @csrf
                <div id="formMethod" style="display: none;"></div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name') }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               placeholder="Enter full name">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ old('email') }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                               placeholder="Enter email address">
                    </div>

                    <div id="passwordFields">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="Enter password">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="Confirm password">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <select name="role_id" id="role_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeUserModal()"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                        <span id="submitButtonText">Create User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentEditingUserId = null;

        function openAddUserModal() {
            currentEditingUserId = null;
            document.getElementById('modalTitle').textContent = 'Add New User';
            document.getElementById('submitButtonText').textContent = 'Create User';
            document.getElementById('userForm').action = "{{ route('settings.users.store') }}";
            document.getElementById('formMethod').innerHTML = '';

            // Reset form
            document.getElementById('userForm').reset();
            document.getElementById('passwordFields').style.display = 'block';
            document.getElementById('password').required = true;
            document.getElementById('password_confirmation').required = true;

            document.getElementById('addUserModal').classList.remove('hidden');
        }

        function openEditUserModal(userId, userName, userEmail, userRoleId) {
            currentEditingUserId = userId;
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('submitButtonText').textContent = 'Update User';
            document.getElementById('userForm').action = `/settings/users/${userId}`;
            document.getElementById('formMethod').innerHTML = '@method('PUT')';

            // Fill form with user data
            document.getElementById('name').value = userName;
            document.getElementById('email').value = userEmail;
            document.getElementById('role_id').value = userRoleId;

            // Hide password fields for edit
            document.getElementById('passwordFields').style.display = 'none';
            document.getElementById('password').required = false;
            document.getElementById('password_confirmation').required = false;

            document.getElementById('addUserModal').classList.remove('hidden');
        }

        function closeUserModal() {
            document.getElementById('addUserModal').classList.add('hidden');
            currentEditingUserId = null;
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

        // Handle form submission
        document.getElementById('userForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;

            // Show loading state
            submitButton.innerHTML = '<i class="lni lni-spinner animate-spin mr-2"></i>Processing...';
            submitButton.disabled = true;

            fetch(form.action, {
                method: 'POST', // Always use POST, Laravel will handle PUT via _method
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        showNotification(data.message || 'Operation completed successfully!', 'success');
                        closeUserModal();

                        // Reload the page after a short delay to see the changes
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showNotification(data.message || 'Operation failed!', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    // Handle validation errors
                    if (error.errors) {
                        const firstError = Object.values(error.errors)[0][0];
                        showNotification(firstError, 'error');
                    } else {
                        showNotification(error.message || 'An error occurred!', 'error');
                    }
                })
                .finally(() => {
                    // Restore button state
                    submitButton.innerHTML = originalButtonText;
                    submitButton.disabled = false;
                });
        });

        // Close modal when clicking outside
        document.getElementById('addUserModal').addEventListener('click', function (e) {
            if (e.target.id === 'addUserModal') {
                closeUserModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeUserModal();
            }
        });
    </script>
@endpush
