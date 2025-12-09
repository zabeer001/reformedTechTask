<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustRequest extends FormRequest
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
            'sku' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'required_with:product_id'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'required_with:product_id'],
            'image' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
