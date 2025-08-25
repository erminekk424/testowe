<?php

use App\Http\Controllers\Api\PaymentsController;
use Illuminate\Support\Facades\Route;

// use App\Enums\PaymentMethod;
// use App\Models\Order;
// use App\Models\Payment;
// use App\Models\Product;
// use App\Models\User;
// use App\Services\HotPayService;

// use PrinsFrank\Standards\Currency\CurrencyAlpha3;

Route::post('/payments/{paymentMethod}/notify', PaymentsController::class);

// Route::get('/payments/{paymentMethod}/generate', function (PaymentMethod $paymentMethod) {
//    $hotpay = new HotPayService($paymentMethod);
//
//    $product = Product::first();
//    $user = User::first();
//
//    $order = Order::create([
//        'user_id' => $user->id,
//        'amount' => 2137,
//    ]);
//
//    $order->items()->create([
//        'product_id' => $product->id,
//        'quantity' => 1,
//    ]);
//
//    /* @var Payment $payment */
//    $payment = $order->payments()->create([
//        'user_id' => $user->id,
//        'amount' => 6969, // PO PRZEWALUTOWANIU NA WALUTE OPERATORA!
//        'currency' => CurrencyAlpha3::Zloty,
//        'method' => $paymentMethod,
//    ]);
//
//    return $hotpay->generatePayment(
//        amount: $payment->amount->getAmount()->toFloat(),
//        description: "Zamówienie $order->uuid",
//        redirectUrl: route('orders.show', $order),
//        orderId: $payment->uuid,
//        email: $user->email,
//        personalData: $user->name,
//    );
// });

Route::fallback(fn () => response()->json(['message' => 'Not Found'], 404));
