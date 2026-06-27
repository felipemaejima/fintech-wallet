<?php

namespace App\Modules\Wallet\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListTransactionsRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'type' => ['nullable', 'in:credit,debit'],
      'start_date' => ['nullable', 'date'],
      'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
      'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
    ];
  }

  public function messages(): array
  {
    return [
      'type.in' => 'O tipo deve ser "credit" ou "debit".',
      'end_date.after_or_equal' => 'A data final deve ser igual ou posterior à inicial.',
    ];
  }
}
