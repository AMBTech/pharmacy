<?php

namespace App\Http\Controllers;

use App\Models\StorageLocation;
use Illuminate\Http\Request;

class StorageSettingController extends Controller
{
    /**
     * Display a listing of storage locations
     */
    public function index()
    {
        $storageLocations = StorageLocation::orderBy('bucket_code')
            ->orderBy('shelf_code')
            ->orderBy('slot_code')
            ->get();

        return view('settings.storage-locations', compact('storageLocations'));
    }

    /**
     * Store a newly created storage location
     */
    public function store(Request $request)
    {
        // Only admin or manager can create
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isManager()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create storage locations.'
                ], 403);
            }
            return redirect()->route('settings.storage-locations')
                ->with('error', 'You do not have permission to create storage locations.');
        }

        $validated = $request->validate([
            'bucket_code' => 'required|string|max:20',
            'shelf_code' => 'required|string|max:20',
            'slot_code' => 'nullable|string|max:20',
            'label' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Check for duplicate location
        $exists = StorageLocation::where('bucket_code', $validated['bucket_code'])
            ->where('shelf_code', $validated['shelf_code'])
            ->where('slot_code', $validated['slot_code'])
            ->exists();

        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This storage location already exists.'
                ], 422);
            }
            return redirect()->back()
                ->with('error', 'This storage location already exists.');
        }

        $storageLocation = StorageLocation::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Storage location created successfully.',
                'storage_location' => $storageLocation
            ]);
        }

        return redirect()->route('settings.storage-locations')
            ->with('success', 'Storage location created successfully.');
    }

    /**
     * Update the specified storage location
     */
    public function update(Request $request, StorageLocation $storageLocation)
    {
        // Only admin or manager can update
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isManager()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update storage locations.'
                ], 403);
            }
            return redirect()->route('settings.storage-locations')
                ->with('error', 'You do not have permission to update storage locations.');
        }

        $validated = $request->validate([
            'bucket_code' => 'required|string|max:20',
            'shelf_code' => 'required|string|max:20',
            'slot_code' => 'nullable|string|max:20',
            'label' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Check for duplicate location (excluding current record)
        $exists = StorageLocation::where('bucket_code', $validated['bucket_code'])
            ->where('shelf_code', $validated['shelf_code'])
            ->where('slot_code', $validated['slot_code'])
            ->where('id', '!=', $storageLocation->id)
            ->exists();

        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This storage location already exists.'
                ], 422);
            }
            return redirect()->back()
                ->with('error', 'This storage location already exists.');
        }

        $storageLocation->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Storage location updated successfully.',
                'storage_location' => $storageLocation
            ]);
        }

        return redirect()->route('settings.storage-locations')
            ->with('success', 'Storage location updated successfully.');
    }

    /**
     * Remove the specified storage location
     */
    public function destroy(StorageLocation $storageLocation)
    {
        // Only admin or manager can delete
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isManager()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete storage locations.'
                ], 403);
            }
            return redirect()->route('settings.storage-locations')
                ->with('error', 'You do not have permission to delete storage locations.');
        }

        // Check if location has associated products
        if ($storageLocation->products()->count() > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete storage location with associated products.'
                ], 403);
            }
            return redirect()->route('settings.storage-locations')
                ->with('error', 'Cannot delete storage location with associated products.');
        }

        $storageLocation->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Storage location deleted successfully.'
            ]);
        }

        return redirect()->route('settings.storage-locations')
            ->with('success', 'Storage location deleted successfully.');
    }
}
