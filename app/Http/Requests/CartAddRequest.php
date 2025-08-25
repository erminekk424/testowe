<?php

namespace App\Http\Requests;

use App\Models\Product;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class CartAddRequest extends FormRequest
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
            'product' => 'required|string|uuid|exists:products,uuid',
            'quantity' => [
                'required',
                'integer:strict',
                function (string $attribute, mixed $value, Closure $fail) {
                    $productUuid = $this->validated('product');
                    $product = Product::select(['min_quantity', 'max_quantity'])->where('uuid', $productUuid)->firstOrFail();

                    if ($value === 0) {
                        return;
                    }

                    $quantity = $product->quantity;

                    $min = $quantity->min;
                    $max = $quantity->max;

                    if ($value < $min) {
                        $fail(__('validation.min.numeric', ['attribute' => $attribute, 'min' => $min]));
                    }

                    if ($value > $max) {
                        $fail(__('validation.max.numeric', ['attribute' => $attribute, 'max' => $max]));
                    }
                },
            ],
        ];
    }
}
