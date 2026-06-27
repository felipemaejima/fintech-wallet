<?php

namespace App\Modules\Wallet\Enums;

enum TransactionTypeEnum: string
{
  case CREDIT = "credit";
  case DEBIT = "debit";

}
