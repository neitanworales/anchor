<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportAddExpenseRequest extends FormRequest
{
  public function authorize(): bool { return true; }

  public function rules(): array
  {
    return [
      'expense_id' => ['required','integer','exists:expenses,id'],
    ];
  }
}