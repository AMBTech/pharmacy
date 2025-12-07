@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Database Backups</h1>
            <div class="flex gap-3">
                <form action="{{ route('backups.create') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        Create New Backup
                    </button>
                </form>

                <button type="button" onclick="document.getElementById('restoreModal').classList.remove('hidden')"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Restore from File
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">File Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($backups as $backup)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $backup['name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $backup['size'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $backup['date'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex gap-2">
                                <a href="{{ route('backups.download', $backup['name']) }}"
                                   class="text-blue-600 hover:text-blue-900">Download</a>
                                <form action="{{ route('backups.delete', $backup['name']) }}" method="POST"
                                      onsubmit="return confirm('Delete this backup?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            No backups found
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Restore Modal -->
    <div id="restoreModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Restore Database</h3>
                <p class="text-sm text-gray-500 mb-4">
                    <strong>Warning:</strong> This will overwrite your current database!
                </p>

                <form action="{{ route('backups.restore') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select Backup File (.sql or .sql.gz)
                        </label>
                        <input type="file" name="backup_file" accept=".sql,.gz" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button"
                                onclick="document.getElementById('restoreModal').classList.add('hidden')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">
                            Cancel
                        </button>
                        <button type="submit"
                                onclick="return confirm('Are you sure? This will overwrite ALL current data!')"
                                class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                            Restore Database
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
