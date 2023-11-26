<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductBrandRequest extends FormRequest
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
    if ($this->routeIs('admin.product-brands.store')) {
      return [
        'name' => [
          'required',
          'string',
          'min:1',
          'max:100',
          'unique:product_brands',
        ],
      ];
    }

    if ($this->routeIs('admin.product-brands.update')) {
      return [
        'name' => [
          'required',
          'string',
          'min:1',
          'max:100',
          'unique:product_brands,name,' . $this->route('product_brand')->id,
        ],
      ];
    }

    if ($this->routeIs('admin.product-brands.massDestroy')) {
      return [
        'ids'   => 'required|array',
        'ids.*' => 'exists:product_brands,id',
      ];
    }
  }
}
