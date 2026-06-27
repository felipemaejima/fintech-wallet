<?php

namespace App\Modules\Wallet\Enums;

enum TransactionStatusEnum: string
{
  case PENDING = "pending";
  case COMPLETED = "completed";
  case FAILED = "failed";

}
