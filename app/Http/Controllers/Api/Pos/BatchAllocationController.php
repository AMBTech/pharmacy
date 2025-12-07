<?php

namespace App\Http\Controllers\Api\Pos;

use App\Http\Controllers\Controller;

use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BatchAllocationController extends Controller
{
    /**
     * Allocate batches for a product (FEFO then FIFO fallback).
     * Request: { product_id: int, qty: int }
     * Response: { allocations: [{batch_id, qty}], remaining }
     */
    public function allocate(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'qty' => 'required|integer|min:1',
        ]);

        $requiredQty = $data['qty'];
        $now = Carbon::now()->toDateString();


        $batches = ProductBatch::where('product_id', $data['product_id'])
            ->where('quantity', '>', 0)
            ->where(function($q) use ($now) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $now);
            })
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();


        $allocations = [];
        $remaining = $requiredQty;


        foreach ($batches as $batch) {
            if ($remaining <= 0) break;
            $take = min($batch->quantity, $remaining);
            if ($take <= 0) continue;
            $allocations[] = ['batch_id' => $batch->id, 'qty' => (int)$take];
            $remaining -= $take;
        }


        return response()->json([
            'allocations' => $allocations,
            'remaining' => $remaining,
        ]);
    }
}
