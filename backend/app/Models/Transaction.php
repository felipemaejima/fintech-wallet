<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Casts;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
  'wallet_id',
  'type',
  'amount',
  'balance_after',
  'status',
])]
class Transaction extends Model
{
  use HasFactory, HasUuids;

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'amount' => 'decimal:2',
      'balance_after' => 'decimal:2',
    ];
  }

  public function wallet(): BelongsTo
  {
    return $this->belongsTo(Wallet::class);
  }
}
