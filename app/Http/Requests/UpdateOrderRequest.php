<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'ordered_at' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(OrderStatus::values())],
            'items' => ['required', 'array', 'min:1'],
            'items.*.stock_id' => ['required', 'integer', 'exists:stocks,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
