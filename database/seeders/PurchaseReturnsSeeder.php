<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseOrder;

class PurchaseReturnsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if there are any purchase orders with received items
        $orders = PurchaseOrder::where('status', 'received')
            ->orWhere('status', 'partial')
            ->with(['items' => function($query) {
                $query->where('received_quantity', '>', 0);
            }])
            ->take(5)
            ->get();

        if ($orders->isEmpty()) {
            $this->command->info('No suitable purchase orders found for creating returns.');
            return;
        }

        foreach ($orders as $order) {
            // Only create returns for orders that have received items
            if ($order->items->where('received_quantity', '>', 0)->isEmpty()) {
                continue;
            }

            // Create 1-2 return records per order
            $returnCount = rand(1, 2);

            for ($i = 0; $i < $returnCount; $i++) {
                $return = PurchaseReturn::factory()->create([
                    'purchase_order_id' => $order->id,
                    'return_date' => now()->subDays(rand(1, 30)),
                    'status' => ['pending', 'approved', 'completed'][rand(0, 2)],
                ]);

                // Add 1-3 items to each return
                $itemsCount = rand(1, min(3, $order->items->count()));
                $selectedItems = $order->items->where('received_quantity', '>', 0)->random($itemsCount);

                foreach ($selectedItems as $orderItem) {
                    // Calculate max returnable quantity (received - already returned)
                    $maxReturnable = $orderItem->received_quantity - ($orderItem->returned_quantity ?? 0);

                    if ($maxReturnable > 0) {
                        $returnQuantity = min($maxReturnable, rand(1, $maxReturnable));

                        $return->items()->create([
                            'purchase_order_item_id' => $orderItem->id,
                            'quantity' => $returnQuantity,
                            'unit_cost' => $orderItem->unit_cost,
                            'total_cost' => $returnQuantity * $orderItem->unit_cost,
                            'reason_type' => ['damaged', 'defective', 'expired', 'wrong_item', 'excess_quantity'][rand(0, 4)],
                            'reason' => $this->getRandomReason(),
                        ]);
                    }
                }

                // Recalculate return totals
                $return->calculateTotals();
            }
        }

        $this->command->info('Purchase returns seeded successfully!');
    }

    /**
     * Get a random return reason.
     */
    private function getRandomReason(): string
    {
        $reasons = [
            'Product arrived damaged during shipping.',
            'Item defective - does not function as described.',
            'Product expired before use.',
            'Received wrong item/size/color.',
            'Excess quantity received beyond order.',
            'Quality does not meet specifications.',
            'Packaging was damaged, product compromised.',
            'Late delivery, no longer needed.',
            'Product recalled by manufacturer.',
            'Customer changed requirements.',
        ];

        return $reasons[array_rand($reasons)];
    }
}
