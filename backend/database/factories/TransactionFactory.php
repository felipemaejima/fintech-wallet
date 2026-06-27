<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{

  public function definition(): array
  {
    $amount = fake()->randomFloat(2, 1, 5000);

    return [
      'wallet_id' => Wallet::factory(),
      'type' => fake()->randomElement(['credit', 'debit']),
      'amount' => $amount,
      'balance_after' => fake()->randomFloat(2, 0, 10000),
      'status' => fake()->randomElement(['pending', 'completed', 'failed']),
    ];
  }

  public function credit(): static
  {
    return $this->state(fn(array $attributes): array => [
      'type' => 'credit',
    ]);
  }

  public function debit(): static
  {
    return $this->state(fn(array $attributes): array => [
      'type' => 'debit',
    ]);
  }

  public function completed(): static
  {
    return $this->state(fn(array $attributes): array => [
      'status' => 'completed',
    ]);
  }
}
