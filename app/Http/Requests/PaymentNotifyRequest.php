<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Services\HotPayService;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PaymentNotifyRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

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
            'KWOTA' => 'required|string',
            'ID_PLATNOSCI' => 'required|string',
            'ID_ZAMOWIENIA' => [
                'required',
                'string',
                'uuid',
                Rule::exists('payments', 'uuid')->where(function (Builder $query) {
                    $query->where([
                        'method' => $this->route('paymentMethod'),
                    ]);
                }),
            ],
            'STATUS' => [
                'required',
                'string',
                Rule::in(['SUCCESS', 'PENDING', 'FAILURE']),
            ],
            'SEKRET' => 'required|string',
            'SECURE' => 'required|string',
            'HASH' => 'required|string',
        ];
    }

    private function validateHash(): bool
    {
        /* @var PaymentMethod $paymentMethod */
        $paymentMethod = $this->route('paymentMethod');
        $hotpay = new HotPayService($paymentMethod);

        $array = $this->only([
            'KWOTA',
            'ID_PLATNOSCI',
            'ID_ZAMOWIENIA',
            'STATUS',
            'SECURE',
            'SEKRET',
        ]);

        $hash = $hotpay->generateNotificationHash($array);

        return hash_equals($hash, $this->HASH);
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->validateHash()) {
                    $validator->errors()->add(
                        'HASH',
                        __('The given data was invalid.')
                    );
                }
            },
        ];
    }
}
