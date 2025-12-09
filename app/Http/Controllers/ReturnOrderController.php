<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnOrderController extends Controller
{
    public function index()
    {
        $returns = ReturnOrder::with('sale')->latest()->paginate(12);
        return view('returns.index', compact('returns'));
    }

    public function search()
    {
        return view('returns.search');
    }

    public function search_result(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string'
        ]);

        $sale = Sale::with('items')->where('invoice_number', $request->invoice_number)->first();

        return view('returns.search', compact('sale'));
    }

    public function create(Sale $sale)
    {
        $sale->load('items.batch');

        return view('returns.create', compact('sale'));
    }

    public function store(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'refund_method' => 'required|string',
            'reason' => 'nullable|string',
            'items' => 'required|array',
            'items.*.sale_item_id' => 'required|integer|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:0',
            'refund_amount' => 'nullable', // client value ignored; server recomputes
        ]);

        $itemsInput = $data['items'];

        $totalRefund = 0;
        $hasPositiveQty = false;
        $processedItems = [];

        DB::beginTransaction();
        try {
            foreach ($itemsInput as $input) {
                $saleItem = \App\Models\SaleItem::lockForUpdate()->find($input['sale_item_id']);

                if (! $saleItem) {
                    DB::rollBack();
                    return back()->withErrors(['items' => 'One of the sale items was not found.'])->withInput();
                }

                // Ensure the sale_item belongs to the sale we're creating return for
                if ($saleItem->sale_id != $sale->id) {
                    DB::rollBack();
                    return back()->withErrors(['items' => 'Sale item does not belong to this sale.'])->withInput();
                }

                $reqQty = (int) $input['quantity'];

                if ($reqQty < 0) {
                    DB::rollBack();
                    return back()->withErrors(['items' => 'Quantity must be 0 or greater.'])->withInput();
                }

                // Compute pending qty for this sale_item (pending returns only)
                $pendingQty = \App\Models\ReturnOrderItem::where('sale_item_id', $saleItem->id)
                    ->whereHas('returnOrder', function ($q) {
                        $q->where('status', 'pending');
                    })
                    ->sum('quantity');

                $alreadyRefundedQty = (int) ($saleItem->refunded_quantity ?? 0);
                $available = max(0, $saleItem->quantity - $alreadyRefundedQty - $pendingQty);

                if ($reqQty > $available) {
                    DB::rollBack();
                    return back()->withErrors(['items' => "Requested return quantity ({$reqQty}) exceeds available ({$available}) for item ID {$saleItem->id}."])->withInput();
                }

                if ($reqQty > 0) {
                    $hasPositiveQty = true;
                }

                // Compute unit price using DB values
                $unitPrice = $saleItem->quantity > 0 ? ($saleItem->total_price / $saleItem->quantity) : 0;
                $lineTotal = round($unitPrice * $reqQty, 2);
                $totalRefund += $lineTotal;

                $processedItems[] = [
                    'sale_item' => $saleItem,
                    'quantity' => $reqQty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            if (! $hasPositiveQty) {
                DB::rollBack();
                return back()->withErrors(['items' => 'Please select quantity for at least one item to return.'])->withInput();
            }

            $totalRefund = round($totalRefund, 2);

            // create ReturnOrder with status pending (cashier submitted)
            $return = \App\Models\ReturnOrder::create([
                'sale_id' => $sale->id,
                'return_number' => 'RET-' . now()->format('Ymd') . '-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(4)),
                'refund_amount' => $totalRefund,
                'refund_method' => $data['refund_method'],
                'reason' => $data['reason'] ?? null,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            foreach ($processedItems as $pi) {
                if ($pi['quantity'] <= 0) {
                    continue;
                }

                $return->items()->create([
                    'sale_item_id' => $pi['sale_item']->id,
                    'product_id' => $pi['sale_item']->product_id,
                    'product_name' => $pi['sale_item']->product_name,
                    'unit_price' => $pi['unit_price'],
                    'quantity' => $pi['quantity'],
                    'total_price' => $pi['line_total'],
                ]);

                // Note: Do NOT modify stock or sale_item.refunded_quantity here.
                // Stock & refunded_quantity will be updated when manager approves the return.
            }

            $sale->total_refund = $totalRefund;
            event('transaction.refund.created', [$sale, $request->payment_method]);

            DB::commit();

            return redirect()->route('returns.show', $return)->with('success', 'Return order created and is pending approval.');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Return store failed: '.$e->getMessage());
            return back()->with('error', 'Failed to process return.')->withInput();
        }
    }


    public function store1(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'refund_method' => 'required|string',
            'reason' => 'nullable|string',
            'items' => 'required|array',
            'items.*.sale_item_id' => 'required|integer|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:0',
            'refund_amount' => 'nullable', // we will recompute and ignore client value
        ]);

        $itemsInput = $data['items'];

        $totalRefund = 0;
        $hasPositiveQty = false;
        $processedItems = [];

        foreach ($itemsInput as $input) {
            $saleItem = \App\Models\SaleItem::find($input['sale_item_id']);

            if (!$saleItem) {
                return back()->withErrors(['items' => 'One of the sale items was not found.'])->withInput();
            }

            $reqQty = (int) $input['quantity'];

            // ensure requested quantity is not exceeding sold qty
            if ($reqQty < 0) {
                return back()->withErrors(['items' => 'Quantity must be 0 or greater.'])->withInput();
            }
            if ($reqQty > $saleItem->quantity) {
                return back()->withErrors(['items' => "Requested return quantity ({$reqQty}) exceeds sold quantity ({$saleItem->quantity}) for item ID {$saleItem->id}."])->withInput();
            }

            if ($reqQty > 0) {
                $hasPositiveQty = true;
            }

            // compute unit price using DB values, not trusting client
            $unitPrice = $saleItem->quantity > 0 ? ($saleItem->total_price / $saleItem->quantity) : 0;
            $lineTotal = round($unitPrice * $reqQty, 2);
            $totalRefund += $lineTotal;

            // keep for later processing
            $processedItems[] = [
                'sale_item' => $saleItem,
                'quantity' => $reqQty,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        // Ensure at least one selected item has quantity > 0
        if (! $hasPositiveQty) {
            return back()->withErrors(['items' => 'Please select quantity for at least one item to return.'])->withInput();
        }

        // Round totalRefund
        $totalRefund = round($totalRefund, 2);

        // Now you can use $totalRefund as the authoritative refund amount.
        // Example: create Return model / store return items / adjust stock / create refund transaction etc.
        // Below is pseudocode for demonstration:

        DB::beginTransaction();
        try {
            $return = \App\Models\ReturnOrder::create([
                'sale_id' => $sale->id,
                'return_number' => 'RET-' . now()->format('Ymd') . '-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(4)),
                'refund_amount' => $totalRefund,
                'refund_method' => $data['refund_method'],
                'reason' => $data['reason'] ?? null,
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);

            foreach ($processedItems as $pi) {
                if ($pi['quantity'] <= 0) continue;
                $return->items()->create([
                    'sale_item_id' => $pi['sale_item']->id,
                    'product_id' => $pi['sale_item']->product_id,
                    'product_name' => $pi['sale_item']->product_name,
                    'unit_price' => $pi['unit_price'],
                    'quantity' => $pi['quantity'],
                    'total_price' => $pi['line_total'],
                ]);

                // optional: decrease stock, mark sale item refunded qty etc.
                // $pi['sale_item']->decrement('quantity', $pi['quantity']);
            }

            DB::commit();

            return redirect()->route('returns.show', $return)->with('success', 'Return processed successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Return store failed: '.$e->getMessage());
            return back()->withErrors(['general' => 'Failed to process return.'])->withInput();
        }
    }

    public function approve(Request $request, ReturnOrder $return)
    {
        auth()->user()->hasPermission('can:returns.approve');

        if ($return->status !== 'pending') {
            return redirect()->back()->with('error', 'Return is not pending.');
        }


        try {
            DB::transaction(function () use ($return) {
                $total = 0;
                foreach ($return->items as $ri) {
                    $saleItem = $ri->saleItem; // relation to SaleItem model
                    $avail = $saleItem->quantity - $saleItem->refunded_quantity;

                    if ($ri->quantity > $avail) {
                        throw new \Exception("Return quantity for item {$saleItem->id} exceeds available quantity.");
                    }

                    // increment stock (adjust to your inventory table / location)
                    $product = $saleItem->product;
                    $product->increment('stock', $ri->quantity);

                    // update refunded qty
                    $saleItem->increment('refunded_quantity', $ri->quantity);

                    $total += ($ri->quantity * ($saleItem->quantity ? $saleItem->total_price / $saleItem->quantity : 0));
                }

                event('transaction.refund.created', [$return, $return->refund_method]);

                // update return order
                $return->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);
            });

            return redirect()->route('returns.show', $return)->with('success', 'Return request approved successfully.');
        }
        catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to process request: ' . $e->getMessage());
        }
    }

    /**
     * Reject a return request.
     */
    public function reject(Request $request, ReturnOrder $return)
    {
        auth()->user()->hasPermission('can:returns.reject');

        // Validate the request
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
            'staff_notes' => 'nullable|string|max:500',
        ]);

        // Check if return can be rejected
        if ($return->status !== 'pending') {
            return back()->with('error', 'This return cannot be rejected. Current status: ' . $return->status);
        }

        DB::beginTransaction();

        try {
            // Update return status
            $return->update([
                'status' => 'rejected',
                'staff_notes' => $request->staff_notes ?: 'Rejected: ' . $request->rejection_reason,
                'user_id' => auth()->id(), // Log who rejected it
            ]);

            // Create transaction record for audit trail
            \App\Models\Transaction::create([
                'transaction_type' => 'return_rejection',
                'related_id' => $return->id,
                'related_type' => ReturnOrder::class,
                'amount' => 0, // No refund for rejected returns
                'payment_method' => null,
                'notes' => 'Return #' . $return->return_number . ' rejected. Reason: ' . substr($request->rejection_reason, 0, 100),
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return back()->with('success', 'Return rejected successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Return rejection failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to reject return: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, ReturnOrder $return)
    {

    }

    public function show(ReturnOrder $returnOrder)
    {
        $returnOrder->load('items.product', 'sale');

//        $settings = \App\Models\SystemSetting::getSettings();
        $currency_symbol = get_currency_symbol();

        return view('returns.show', compact('returnOrder', 'currency_symbol'));
    }
}
