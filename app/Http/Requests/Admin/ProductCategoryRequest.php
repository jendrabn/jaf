<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductCategoryRequest extends FormRequest
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
    if ($this->routeIs('admin.product-categories.store')) {
      return [
        'name' => [
          'required',
          'string',
          'min:1',
          'max:100',
          'unique:product_categories',
        ],
      ];
    }

    if ($this->routeIs('admin.product-categories.update')) {
      return [
        'name' => [
          'required',
          'string',
          'min:1',
          'max:100',
          'unique:product_categories,name,' . $this->route('product_category')->id,
        ],
      ];
    }

    if ($this->routeIs('admin.product-categories.massDestroy')) {
      return [
        'ids'   => 'required|array',
        'ids.*' => 'exists:product_categories,id',
      ];
    }
  }
}
