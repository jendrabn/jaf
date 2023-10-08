<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCartRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
   */
  public function rules(): array
  {
    return [
      'product_id' => [
        'required',
        'integer',
        Rule::exists('products', 'id')
          ->where('is_publish', true)
      ],
      'quantity' => [
        'required',
        'integer',
        'min:1'
      ]
    ];
  }
}
