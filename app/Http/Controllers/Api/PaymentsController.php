<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentNotifyRequest;
use App\Models\Payment;

class PaymentsController extends Controller
{
    public function __invoke(PaymentNotifyRequest $request, PaymentMethod $paymentMethod)
    {
        $data = $request->safe();

        $statusEnum = match ($data['STATUS']) {
            'SUCCESS' => PaymentStatus::Success,
            'PENDING' => PaymentStatus::Pending,
            'FAILURE' => PaymentStatus::Failed,
        };

        $payment = Payment::whereUuid($data['ID_ZAMOWIENIA'])->whereMethod($paymentMethod)->first();
        $amount = $payment->amount->getAmount()->toFloat();

        $adequateAmount = $amount <= (float) $data['KWOTA'];

        if (! $adequateAmount) {
            return response('Bad Request', 400);
        }

        $payment->update([
            'external_id' => $data['ID_PLATNOSCI'],
            'status' => $statusEnum,
        ]);

        return response('OK', 200);
    }
}
