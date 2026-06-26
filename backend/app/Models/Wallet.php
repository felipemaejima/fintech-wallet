<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Casts;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'balance'])]
#[Casts(['balance' => 'decimal:2'])]
class Wallet extends Model
{
  use HasUuids;

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function transactions(): HasMany
  {
    return $this->hasMany(Transaction::class);
  }
}
