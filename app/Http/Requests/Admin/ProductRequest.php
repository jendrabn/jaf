<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
    if ($this->routeIs('admin.products.store')) {

      return [
        'images' => [
          'array',
          'required',
        ],
        'images.*' => [
          'required',
        ],
        'name' => [
          'required',
          'string',
          'min:1',
          'max:200',
        ],
        'product_category_id' => [
          'required',
          'integer',
          'exists:product_categories,id'
        ],
        'description' => [
          'required',
        ],
        'product_brand_id' => [
          'nullable',
          'integer',
          'exists:product_brands,id'
        ],
        'sex' => [
          'nullable',
          'integer',
          'in:1,2,3'
        ],
        'price' => [
          'required',
          'integer',
        ],
        'stock' => [
          'required',
          'integer',
        ],
        'weight' => [
          'required',
          'integer',
        ],
        'is_publish' => [
          'required',
        ],
      ];
    }

    if ($this->routeIs('admin.products.update')) {
      return [
        'images' => [
          'array',
          'required',
        ],
        'images.*' => [
          'required',
        ],
        'name' => [
          'required',
          'string',
          'min:1',
          'max:200',
        ],
        'product_category_id' => [
          'required',
          'integer',
          'exists:product_categories,id'
        ],
        'description' => [
          'required',
        ],
        'product_brand_id' => [
          'nullable',
          'integer',
          'exists:product_brands,id'
        ],
        'sex' => [
          'nullable',
          'integer',
          'in:1,2,3'
        ],
        'price' => [
          'required',
          'integer',
        ],
        'stock' => [
          'required',
          'integer',
        ],
        'weight' => [
          'required',
          'integer',
        ],
        'is_publish' => [
          'required',
        ],
      ];
    }

    if ($this->routeIs('admin.products.massDestroy')) {
      return [
        'ids'   => 'required|array',
        'ids.*' => 'exists:products,id',
      ];
    }
  }

  /**
   * Prepare the data for validation.
   *
   * @return void
   */
  protected function prepareForValidation()
  {
    $this->merge(['is_publish' => (bool) $this->is_publish]);
  }
}
