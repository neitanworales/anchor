<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseStoreRequest extends FormRequest
{
  public function authorize(): bool { return true; }

  public function rules(): array
  {
    return [
      'category_id' => ['required','integer','exists:categories,id'],
      'cost_center_id' => ['nullable','integer','exists:cost_centers,id'],
      'expense_date' => ['required','date'],
      'vendor' => ['nullable','string','max:255'],
      'currency' => ['nullable','string','size:3'],
      'amount' => ['required','numeric','min:0.01'],
      'tax_iva' => ['nullable','numeric','min:0'],
      'payment_method' => ['nullable','string','max:30'],
      'receipt_type' => ['required','in:TICKET,CFDI'],
    ];
  }
}