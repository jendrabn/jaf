<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;


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
                    'unique:product_brands'
                ],
                'slug' => [
                    'required',
                    'string',
                    'min:1',
                    'max:255'
                ],
            ];
        } else if ($this->routeIs('admin.product-brands.update')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:100',
                    'unique:product_brands,name,' . $this->route('product_brand')->id,
                ],
                'slug' => [
                    'required',
                    'string',
                    'min:1',
                    'max:255'
                ],
            ];
        } else if ($this->routeIs('admin.product-brands.massDestroy')) {
            return [
                'ids' => [
                    'required',
                    'array'
                ],
                'ids.*' => [
                    'integer',
                    'exists:product_brands,id'
                ],
            ];
        } else {
            return [];
        }

    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->name),
        ]);
    }
}
