<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PurchaseReturn;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseReturn>
 */
class PurchaseReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'return_number' => 'RET-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'return_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'return_type' => $this->faker->randomElement(['full_refund', 'partial_refund', 'replacement', 'store_credit']),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'completed']),
            'reason' => $this->faker->sentence(),
            'subtotal' => 0,
            'restocking_fee' => $this->faker->randomFloat(2, 0, 15),
            'shipping_cost' => $this->faker->randomFloat(2, 0, 50),
            'total' => 0,
            'notes' => $this->faker->optional()->paragraph(),
            'approved_at' => function (array $attributes) {
                return in_array($attributes['status'], ['approved', 'completed'])
                    ? $this->faker->dateTimeBetween('-30 days', 'now')
                    : null;
            },
            'completed_at' => function (array $attributes) {
                return $attributes['status'] === 'completed'
                    ? $this->faker->dateTimeBetween('-30 days', 'now')
                    : null;
            },
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): self
    {
        return $this->afterMaking(function (PurchaseReturn $return) {
            // Ensure return number is unique
            while (PurchaseReturn::where('return_number', $return->return_number)->exists()) {
                $return->return_number = 'RET-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
