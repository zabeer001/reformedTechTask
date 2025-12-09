<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'barcode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'barcode')->ignore($this->product),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($this->product),
            ],
            'category' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],

            // stocks sync: allow updating/creating stocks alongside product
            'stocks' => ['nullable', 'array'],
            'stocks.*.id' => ['nullable', 'integer', 'exists:stocks,id'],
            'stocks.*.sku' => ['nullable', 'string', 'max:255'],
            'stocks.*.sale_price' => ['nullable', 'numeric'],
            'stocks.*.purchase_price' => ['nullable', 'numeric'],
            'stocks.*.quantity' => ['nullable', 'integer'],
            'stocks.*.image' => ['nullable', 'image', 'max:5120'],
            'stocks.*.image_path' => ['nullable', 'string'],
        ];
    }
}
