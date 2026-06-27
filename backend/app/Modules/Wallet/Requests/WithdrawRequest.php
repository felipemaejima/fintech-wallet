<?php

namespace App\Modules\Wallet\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'amount' => ['required', 'numeric', 'min:0.01', 'decimal:0,2'],
    ];
  }

  public function messages(): array
  {
    return [
      'amount.min' => 'O valor mínimo para saque é R$ 0,01.',
      'amount.decimal' => 'O valor não pode ter mais de 2 casas decimais.',
      'amount.numeric' => 'O valor deve ser numérico.',
    ];
  }
}
