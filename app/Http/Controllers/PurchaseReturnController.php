<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        $purchaseReturns = PurchaseReturn::query();
        $purchaseReturns = $purchaseReturns->paginate(20);
        return view('purchases.returns.index', compact('purchaseReturns'));
    }

    public function create(Request $request)
    {
        // Get purchase orders that have received items and are not completed/cancelled
        $purchaseOrders = PurchaseOrder::query()
            ->whereIn('status', ['received', 'partial'])
            ->whereHas('items', function($query) {
                $query->where('received_quantity', '>', DB::raw('COALESCE(returned_quantity, 0)'))
                    ->where('received_quantity', '>', 0);
            })
            ->with(['supplier', 'items' => function($query) {
                $query->where('received_quantity', '>', DB::raw('COALESCE(returned_quantity, 0)'))
                    ->with(['product']);
            }])
            ->orderBy('order_date', 'desc')
            ->get();

        // If a specific purchase order is requested via query parameter
        $selectedPurchaseOrder = null;
        if ($request->has('purchase_order_id')) {
            $selectedPurchaseOrder = PurchaseOrder::find($request->purchase_order_id);
        }

        // Get currencies for formatting
        $currency = get_currency_symbol();

        return view('purchases.returns.create', compact('purchaseOrders', 'selectedPurchaseOrder', 'currency'));
    }

    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'return_date' => 'required|date',
            'return_type' => 'required|in:full_refund,partial_refund,replacement,store_credit',
            'status' => 'required|in:pending,approved,rejected,completed',
            'reason' => 'nullable|string|max:500',
            'restocking_fee' => 'required|numeric|min:0|max:100',
            'shipping_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.reason_type' => 'required|in:damaged,defective,expired,wrong_item,excess_quantity,other',
            'items.*.reason' => 'nullable|string',
        ]);

        // Check if purchase order exists and has received items
        $purchaseOrder = PurchaseOrder::findOrFail($validated['purchase_order_id']);

        if (!in_array($purchaseOrder->status, ['received', 'partial'])) {
            return response()->json([
                'errors' => ['purchase_order_id' => ['Selected purchase order does not have received items.']]
            ], 422);
        }

        // Validate that items belong to the selected purchase order
        $purchaseOrderItemIds = $purchaseOrder->items->pluck('id')->toArray();
        foreach ($validated['items'] as $index => $item) {
            if (!in_array($item['purchase_order_item_id'], $purchaseOrderItemIds)) {
                return response()->json([
                    'errors' => ["items.$index.purchase_order_item_id" => ['Item does not belong to selected purchase order.']]
                ], 422);
            }
        }

        // Validate quantity constraints
        foreach ($validated['items'] as $index => $itemData) {
            $purchaseOrderItem = PurchaseOrderItem::findOrFail($itemData['purchase_order_item_id']);

            $availableQuantity = $purchaseOrderItem->received_quantity - ($purchaseOrderItem->returned_quantity ?? 0);

            if ($itemData['quantity'] > $availableQuantity) {
                return response()->json([
                    'errors' => ["items.$index.quantity" => ["Quantity exceeds available quantity ({$availableQuantity})."]]
                ], 422);
            }

            if ($itemData['quantity'] <= 0) {
                return response()->json([
                    'errors' => ["items.$index.quantity" => ["Quantity must be greater than 0."]]
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            // Generate return number
            $returnNumber = 'RET-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Check for duplicate return number (very unlikely but possible)
            while (PurchaseReturn::where('return_number', $returnNumber)->exists()) {
                $returnNumber = 'RET-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }

            // Create the purchase return
            $purchaseReturn = PurchaseReturn::create([
                'return_number' => $returnNumber,
                'purchase_order_id' => $validated['purchase_order_id'],
                'return_date' => $validated['return_date'],
                'return_type' => $validated['return_type'],
                'status' => $validated['status'],
                'reason' => $validated['reason'],
                'restocking_fee' => $validated['restocking_fee'],
                'shipping_cost' => $validated['shipping_cost'],
                'notes' => $validated['notes'] ?? '',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $subtotal = 0;

            // Create return items
            foreach ($validated['items'] as $itemData) {
                $totalCost = $itemData['quantity'] * $itemData['unit_cost'];
                $subtotal += $totalCost;

                $returnItem = $purchaseReturn->items()->create([
                    'purchase_order_item_id' => $itemData['purchase_order_item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost'],
                    'total_cost' => $totalCost,
                    'reason_type' => $itemData['reason_type'],
                    'reason' => $itemData['reason'],
                ]);

                // Update returned quantity on purchase order item
                DB::table('purchase_order_items')
                    ->where('id', $itemData['purchase_order_item_id'])
                    ->update([
                        'returned_quantity' => DB::raw("COALESCE(returned_quantity, 0) + {$itemData['quantity']}"),
                        'updated_at' => now(),
                    ]);
            }

            // Calculate final totals
            $feeAmount = $subtotal * ($validated['restocking_fee'] / 100);
            $total = $subtotal - $feeAmount - $validated['shipping_cost'];

            // Update return with calculated totals
            $purchaseReturn->update([
                'subtotal' => $subtotal,
                'total' => $total,
            ]);

            // If status is approved or completed, process accordingly
            if ($validated['status'] === 'approved') {
                $purchaseReturn->update([
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                // Handle replacement orders if needed
                if ($validated['return_type'] === 'replacement') {
                    $this->createReplacementOrder($purchaseReturn);
                }

                // Adjust inventory for defective/damaged items
                foreach ($purchaseReturn->items as $item) {
                    if (in_array($item->reason_type, ['defective', 'damaged', 'expired'])) {
                        $product = $item->purchaseOrderItem->product;
                        $product->decrement('stock', $item->quantity);
                    }
                }
            } elseif ($validated['status'] === 'completed') {
                $purchaseReturn->update([
                    'completed_at' => now(),
                ]);

                // Process refund if applicable
                if (in_array($validated['return_type'], ['full_refund', 'partial_refund', 'store_credit'])) {
                    $this->processRefund($purchaseReturn, [
                        'actual_refund_amount' => $total,
                        'refund_date' => $validated['return_date'],
                    ]);
                }

                // Update supplier statistics
                $this->updateSupplierStats($purchaseReturn);
            }

            // Update purchase order status if all items are returned
            $this->updatePurchaseOrderStatus($purchaseOrder);

            DB::commit();

            // Send notification if needed
            /*if (config('app.send_return_notifications')) {
                $this->sendReturnNotification($purchaseReturn);
            }*/

            return response()->json([
                'success' => true,
                'message' => 'Purchase return created successfully.',
                'redirect' => route('purchases.returns.show', $purchaseReturn),
                'return_id' => $purchaseReturn->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to create purchase return: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'errors' => ['general' => ['An error occurred while creating the return. Please try again.']],
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load([
            'purchaseOrder.supplier',
            'items.purchaseOrderItem.product',
            'creator',
            'approver'
        ]);
        return view('purchases.returns.show', compact('purchaseReturn'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseReturn $purchaseReturn)
    {
        // Only allow editing of pending returns
        if ($purchaseReturn->status !== 'pending') {
            return redirect()->route('purchases.returns.show', $purchaseReturn)
                ->with('error', 'Only pending returns can be edited.');
        }

        $purchaseReturn->load([
            'items.purchaseOrderItem.product',
            'purchaseOrder.supplier'
        ]);

        // Get purchase orders that have received items
        $purchaseOrders = PurchaseOrder::whereIn('status', ['received', 'partial'])
            ->whereHas('items', function ($query) {
                $query->where('received_quantity', '>', 0);
            })
            ->with(['supplier'])
            ->get();

        // Get products for the selected purchase order
        $products = [];
        if ($purchaseReturn->purchaseOrder) {
            $products = $purchaseReturn->purchaseOrder->items()
                ->where('received_quantity', '>', 0)
                ->with(['product'])
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'stock' => $item->product->stock,
                        'batch_number' => $item->batch_number,
                        'received_quantity' => $item->received_quantity,
                        'returned_quantity' => $item->returned_quantity,
                        'unit_cost' => $item->unit_cost,
                    ];
                });
        }

        return view('purchases.returns.edit', compact('purchaseReturn', 'purchaseOrders', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseReturn $purchaseReturn)
    {
        // Only allow updating of pending returns
        if ($purchaseReturn->status !== 'pending') {
            return response()->json([
                'errors' => ['general' => ['Only pending returns can be updated.']]
            ], 422);
        }

        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'return_date' => 'required|date',
            'return_type' => 'required|in:full_refund,partial_refund,replacement,store_credit',
            'status' => 'required|in:pending,approved,rejected,completed',
            'reason' => 'nullable|string',
            'restocking_fee' => 'required|numeric|min:0|max:100',
            'shipping_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.reason_type' => 'required|in:damaged,defective,expired,wrong_item,excess_quantity,other',
            'items.*.reason' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Update return header
            $purchaseReturn->update([
                'purchase_order_id' => $validated['purchase_order_id'],
                'return_date' => $validated['return_date'],
                'return_type' => $validated['return_type'],
                'status' => $validated['status'],
                'reason' => $validated['reason'],
                'restocking_fee' => $validated['restocking_fee'],
                'shipping_cost' => $validated['shipping_cost'],
                'notes' => $validated['notes'],
            ]);

            // Get existing items to track for quantity adjustments
            $existingItems = $purchaseReturn->items->keyBy('purchase_order_item_id');
            $updatedItemIds = [];

            // Update or create items
            foreach ($validated['items'] as $itemData) {
                // Check if item already exists
                $existingItem = $existingItems->get($itemData['purchase_order_item_id']);

                if ($existingItem) {
                    // Update existing item
                    $quantityDifference = $itemData['quantity'] - $existingItem->quantity;

                    $existingItem->update([
                        'quantity' => $itemData['quantity'],
                        'unit_cost' => $itemData['unit_cost'],
                        'reason_type' => $itemData['reason_type'],
                        'reason' => $itemData['reason'],
                    ]);

                    // Adjust returned quantity on purchase order item
                    $this->adjustReturnedQuantity(
                        $itemData['purchase_order_item_id'],
                        $quantityDifference
                    );

                    $updatedItemIds[] = $existingItem->id;
                } else {
                    // Create new item
                    $returnItem = $purchaseReturn->items()->create([
                        'purchase_order_item_id' => $itemData['purchase_order_item_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_cost' => $itemData['unit_cost'],
                        'reason_type' => $itemData['reason_type'],
                        'reason' => $itemData['reason'],
                    ]);

                    // Update returned quantity on purchase order item
                    $this->adjustReturnedQuantity(
                        $itemData['purchase_order_item_id'],
                        $itemData['quantity']
                    );

                    $updatedItemIds[] = $returnItem->id;
                }
            }

            // Delete items that were removed
            $itemsToDelete = $existingItems->whereNotIn('id', $updatedItemIds);
            foreach ($itemsToDelete as $itemToDelete) {
                // Adjust returned quantity back
                $this->adjustReturnedQuantity(
                    $itemToDelete->purchase_order_item_id,
                    -$itemToDelete->quantity
                );

                $itemToDelete->delete();
            }

            // Recalculate totals
            $purchaseReturn->calculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Return updated successfully.',
                'redirect' => route('purchases.returns.show', $purchaseReturn)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'errors' => ['general' => ['An error occurred while updating the return: ' . $e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseReturn $purchaseReturn)
    {
        // Only allow deletion of pending returns
        if ($purchaseReturn->status !== 'pending') {
            return redirect()->route('purchases.returns.show', $purchaseReturn)
                ->with('error', 'Only pending returns can be deleted.');
        }

        DB::beginTransaction();

        try {
            // First, adjust returned quantities on purchase order items
            foreach ($purchaseReturn->items as $item) {
                $this->adjustReturnedQuantity(
                    $item->purchase_order_item_id,
                    -$item->quantity
                );
            }

            // Delete the return
            $purchaseReturn->delete();

            DB::commit();

            return redirect()->route('purchases.returns.index')
                ->with('success', 'Return deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('purchases.returns.show', $purchaseReturn)
                ->with('error', 'An error occurred while deleting the return: ' . $e->getMessage());
        }
    }

    /**
     * Approve a purchase return.
     */
    public function approve(Request $request, PurchaseReturn $purchaseReturn)
    {
        // Only allow approval of pending returns
        if ($purchaseReturn->status !== 'pending') {
            return redirect()->route('purchases.returns.show', $purchaseReturn)
                ->with('error', 'Only pending returns can be approved.');
        }

        // Check if user has permission to approve
        if (!auth()->user()->can('approve', $purchaseReturn)) {
            return redirect()->route('purchases.returns.show', $purchaseReturn)
                ->with('error', 'You do not have permission to approve returns.');
        }

        DB::beginTransaction();

        try {
            // Update return status
            $purchaseReturn->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // If return type is replacement, create a new purchase order
            if ($purchaseReturn->return_type === 'replacement') {
                $this->createReplacementOrder($purchaseReturn);
            }

            // Update inventory for defective/damaged/expired items
            foreach ($purchaseReturn->items as $item) {
                if (in_array($item->reason_type, ['defective', 'damaged', 'expired'])) {
                    $product = $item->purchaseOrderItem->product;
                    $product->decrement('stock', $item->quantity);

                    // Optional: Log the stock adjustment if you have a logging system
                    // $this->logStockAdjustment($product, -$item->quantity, $purchaseReturn);
                }
            }

            event('transaction.purchase.return', [$purchaseReturn]);

            DB::commit();

            return redirect()->route('purchases.returns.show', $purchaseReturn)
                ->with('success', 'Return approved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('purchases.returns.show', $purchaseReturn)
                ->with('error', 'An error occurred while approving the return: ' . $e->getMessage());
        }
    }

    /**
     * Reject a purchase return.
     */
    public function reject(Request $request, PurchaseReturn $purchaseReturn)
    {
        // Only allow rejection of pending returns
        if ($purchaseReturn->status !== 'pending') {
            return redirect()->route('purchases.returns.show', $purchaseReturn)
                ->with('error', 'Only pending returns can be rejected.');
        }

        // Validate rejection reason
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ]);

        DB::beginTransaction();

        try {
            // Update return status
            $purchaseReturn->update([
                'status' => 'rejected',
                'notes' => $purchaseReturn->notes . "\n\nRejection Reason: " . $validated['rejection_reason'],
            ]);

            DB::commit();

            return redirect()->route('purchases.returns.show', $purchaseReturn)
                ->with('success', 'Return rejected successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('purchases.returns.show', $purchaseReturn)
                ->with('error', 'An error occurred while rejecting the return: ' . $e->getMessage());
        }
    }

    /**
     * Complete a purchase return.
     */
    public function complete(Request $request, PurchaseReturn $purchaseReturn)
    {
        // Only allow completion of approved returns
        if ($purchaseReturn->status !== 'approved') {
            return redirect()->route('purchases.returns.show', $purchaseReturn)
                ->with('error', 'Only approved returns can be marked as completed.');
        }

        $validated = $request->validate([
            'completion_notes' => 'nullable|string|max:500',
            'actual_refund_amount' => 'nullable|numeric|min:0',
            'refund_date' => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            // Update return status
            $purchaseReturn->update([
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => $purchaseReturn->notes . "\n\nCompletion Notes: " . ($validated['completion_notes'] ?? 'Marked as completed'),
                'total' => $validated['actual_refund_amount'] ?? $purchaseReturn->total,
            ]);

            // Process refund if applicable
            if (in_array($purchaseReturn->return_type, ['full_refund', 'partial_refund', 'store_credit'])) {
                $this->processRefund($purchaseReturn, $validated);
            }

            // Update supplier statistics
            $this->updateSupplierStats($purchaseReturn);

            DB::commit();

            return redirect()->route('purchases.returns.show', $purchaseReturn)
                ->with('success', 'Return marked as completed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('purchases.returns.show', $purchaseReturn)
                ->with('error', 'An error occurred while completing the return: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Adjust returned quantity on purchase order item.
     */
    private function adjustReturnedQuantity($purchaseOrderItemId, $quantity)
    {
        DB::table('purchase_order_items')
            ->where('id', $purchaseOrderItemId)
            ->update([
                'returned_quantity' => DB::raw("COALESCE(returned_quantity, 0) + $quantity"),
                'updated_at' => now(),
            ]);
    }

    /**
     * Helper: Create replacement purchase order.
     */
    private function createReplacementOrder(PurchaseReturn $purchaseReturn)
    {
        $originalOrder = $purchaseReturn->purchaseOrder;

        // Create new purchase order for replacement
        $replacementOrder = PurchaseOrder::create([
            'po_number' => 'REP-' . $purchaseReturn->return_number,
            'supplier_id' => $originalOrder->supplier_id,
            'user_id' => auth()->id(),
            'order_date' => now(),
            'expected_delivery_date' => now()->addDays(7),
            'status' => 'ordered',
            'notes' => "Replacement for Return #{$purchaseReturn->return_number}",
            'is_replacement' => true,
            'original_return_id' => $purchaseReturn->id,
        ]);

        // Add items to replacement order
        foreach ($purchaseReturn->items as $item) {
            $replacementOrder->items()->create([
                'product_id' => $item->purchaseOrderItem->product_id,
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_cost,
                'batch_number' => $item->purchaseOrderItem->batch_number,
            ]);
        }

        $replacementOrder->calculateTotals();

        return $replacementOrder;
    }

    /**
     * Helper: Process refund payment.
     */
    private function processRefund(PurchaseReturn $purchaseReturn, array $data)
    {
        // Get supplier for payment details
        $supplier = $purchaseReturn->purchaseOrder->supplier;

        // Determine refund method based on return type
        $method = 'bank_transfer'; // default
        if ($purchaseReturn->return_type === 'store_credit') {
            $method = 'store_credit';
        }

        // Prepare refund data
        $refundData = [
            'purchase_return_id' => $purchaseReturn->id,
            'purchase_order_id' => $purchaseReturn->purchase_order_id,
            'supplier_id' => $supplier->id,
            'amount' => $data['actual_refund_amount'] ?? $purchaseReturn->total,
            'refund_date' => $data['refund_date'] ?? now(),
            'method' => $method,
            'reference' => 'REF-' . $purchaseReturn->return_number,
            'status' => 'completed',
            'notes' => 'Refund for purchase return #' . $purchaseReturn->return_number,
            'created_by' => auth()->id(),
            'completed_at' => now(),
        ];

        // Add payment method specific details
        if ($method === 'bank_transfer' && $supplier->bank_details) {
            $refundData['bank_name'] = $supplier->bank_name;
            $refundData['account_name'] = $supplier->account_name;
            $refundData['account_number'] = $supplier->account_number;
        }

        if ($method === 'store_credit') {
            $refundData['credit_balance'] = $refundData['amount'];
            $refundData['credit_expiry_date'] = now()->addYear(); // 1 year expiry
        }

        // Create refund record
        \App\Models\Refund::create($refundData);

        // Update supplier account if using store credit
        if ($method === 'store_credit') {
            DB::table('suppliers')
                ->where('id', $supplier->id)
                ->increment('credit_balance', $refundData['amount']);
        }

        // Update purchase return with refund reference
        $purchaseReturn->update([
            'refund_processed' => true,
            'refund_reference' => $refundData['reference'],
        ]);
    }

    /**
     * Helper: Record inventory adjustment.
     */
    /*private function recordInventoryAdjustment(Product $product, $quantity, $reason, $reference)
    {
        DB::table('inventory_adjustments')->insert([
            'product_id' => $product->id,
            'adjustment_type' => $quantity > 0 ? 'addition' : 'deduction',
            'quantity' => abs($quantity),
            'reason' => $reason,
            'reference' => $reference,
            'adjusted_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }*/

    /**
     * Helper: Update supplier statistics.
     */
    private function updateSupplierStats(PurchaseReturn $purchaseReturn)
    {
        $supplier = $purchaseReturn->purchaseOrder->supplier;

        DB::table('suppliers')
            ->where('id', $supplier->id)
            ->update([
                'total_returns' => DB::raw('COALESCE(total_returns, 0) + 1'),
                'total_return_amount' => DB::raw("COALESCE(total_return_amount, 0) + {$purchaseReturn->total}"),
                'updated_at' => now(),
            ]);
    }

    /**
     * Update purchase order status based on returns.
     */
    private function updatePurchaseOrderStatus(PurchaseOrder $purchaseOrder)
    {
        $allItemsReturned = true;
        $someItemsReturned = false;

        foreach ($purchaseOrder->items as $item) {
            if ($item->received_quantity > 0 && $item->returned_quantity < $item->received_quantity) {
                $allItemsReturned = false;
            }
            if ($item->returned_quantity > 0) {
                $someItemsReturned = true;
            }
        }

        if ($allItemsReturned && $someItemsReturned) {
            $purchaseOrder->update(['status' => 'fully_returned']);
        } elseif ($someItemsReturned) {
            $purchaseOrder->updateReceivedStatus(); // This will set to 'partial' if needed
        }
    }

    /**
     * Send return notification to relevant parties.
     */
    /*private function sendReturnNotification(PurchaseReturn $purchaseReturn)
    {
        try {
            $supplier = $purchaseReturn->purchaseOrder->supplier;
            $users = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['admin', 'manager', 'purchasing_manager']);
            })->get();

            // Send email to supplier if email exists
            if ($supplier->email) {
                Mail::to($supplier->email)->send(new PurchaseReturnCreated($purchaseReturn));
            }

            // Send internal notifications
            foreach ($users as $user) {
                if ($user->email) {
                    Mail::to($user->email)->send(new NewPurchaseReturnNotification($purchaseReturn));
                }

                // Create database notification
                $user->notify(new PurchaseReturnCreatedNotification($purchaseReturn));
            }

            // Log the notification
            activity()
                ->causedBy(auth()->user())
                ->performedOn($purchaseReturn)
                ->log('Return notifications sent');

        } catch (\Exception $e) {
            \Log::error('Failed to send return notification: ' . $e->getMessage());
        }
    }*/

    /**
     * Get returnable items for a purchase order (API endpoint).
     */
    public function getReturnableItems(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['received', 'partial'])) {
            return response()->json([
                'error' => 'Purchase order does not have received items.'
            ], 400);
        }

        $items = $purchaseOrder->items()
            ->where('received_quantity', '>', DB::raw('COALESCE(returned_quantity, 0)'))
            ->with(['product'])
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_code' => $item->product->barcode,
                    'batch_number' => $item->batch_number,
                    'received_quantity' => $item->received_quantity,
                    'returned_quantity' => $item->returned_quantity ?? 0,
                    'unit_cost' => $item->unit_cost,
                    'available' => $item->received_quantity - ($item->returned_quantity ?? 0),
                    'manufacturing_date' => $item->manufacturing_date?->format('Y-m-d'),
                    'expiry_date' => $item->expiry_date?->format('Y-m-d'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $items,
            'purchase_order' => [
                'id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'supplier_name' => $purchaseOrder->supplier->name,
                'order_date' => $purchaseOrder->order_date->format('Y-m-d'),
            ]
        ]);
    }
}
