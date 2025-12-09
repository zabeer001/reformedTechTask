<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'barcode' => ['required', 'string', 'max:255', 'unique:products,barcode'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'category' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],

            // optional stocks payload for creating initial SKUs
            'stocks' => ['nullable', 'array'],
            'stocks.*.sku' => ['nullable', 'string', 'max:255'],
            'stocks.*.sale_price' => ['nullable', 'numeric'],
            'stocks.*.purchase_price' => ['nullable', 'numeric'],
            'stocks.*.quantity' => ['nullable', 'integer'],
            // allow file upload per-stock when sending multipart/form-data
            'stocks.*.image' => ['nullable', 'image', 'max:5120'],
            // allow providing existing path instead of uploading
            'stocks.*.image_path' => ['nullable', 'string'],
        ];
    }
}
