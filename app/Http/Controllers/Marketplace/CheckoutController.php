<?php

namespace App\Http\Controllers\Marketplace;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutStoreRequest;
use App\Models\Product;
use App\Services\HotPayService;
use Brick\Money\Money;
use Cart;
use Darryldecode\Cart\ItemCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Inertia\Inertia;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;
use Swoole\Coroutine\Http\Client;

class CheckoutController extends Controller
{
    public function index()
    {
        //        TODO: make it DRY!
        if (!$cartUuid = Session::get('cart_uuid')) {
            $cartUuid = Str::uuid();
            Session::put('cart_uuid', $cartUuid);
        }
        Cart::session($cartUuid);

        $items = Cart::getContent()->sortBy('price')->map(function (ItemCollection $itemCollection) {
            /* @var Product $associatedModel */
            $associatedModel = $itemCollection->get('associatedModel');

            return [
                ...$itemCollection->only(['id', 'name', 'price', 'quantity']),
                'product' => collect($associatedModel->toResource())->only(['uuid', 'image', 'quantity']),
            ];
        })->values();

        $currencies = $this->getCurrencies();
        $total = round(Cart::getTotal(), 2);

        return Inertia::render('checkout', [
            'checkout' => [
                'items' => Inertia::defer(fn() => $items),
                'total' => Inertia::defer(fn() => $total),
                'rate' => Inertia::defer(fn() => $currencies['PLN']),
            ],
        ]);
    }

    public function store(CheckoutStoreRequest $request)
    {
        $method = $request->enum(key: 'method', enumClass: PaymentMethod::class);
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }
        if (!$cartUuid = Session::get('cart_uuid')) {
            $cartUuid = Str::uuid();
            Session::put('cart_uuid', $cartUuid);
        }
        Cart::session($cartUuid);

        if (Cart::isEmpty()) {
            abort(403);
        }

        $cartContent = Cart::getContent();
        $cartTotal = round(Cart::getTotal(), 2);

        $cartItems = $cartContent->map(function (ItemCollection $itemCollection) {
            /* @var Product $associatedModel */
            $associatedModel = $itemCollection->get('associatedModel');

            return [
                'product_id' => $associatedModel->id,
                'quantity' => $itemCollection->quantity,
                'options' => [
                    'product_price' => $associatedModel->price,
                    'product_quantity' => $associatedModel->quantity,

                    'price_sum' => $itemCollection->getPriceSum(),
                    'price_with_conditions' => $itemCollection->getPriceWithConditions(),
                    'price_sum_with_conditions' => $itemCollection->getPriceSumWithConditions(),

                    'attributes' => $itemCollection->attributes,
                ],
            ];
        })->values();

        $currencies = $this->getCurrencies();

        $paymentCurrency = CurrencyAlpha3::Zloty;
        $paymentTotal = $method->calculateCommission($cartTotal, $currencies[$paymentCurrency->value]);
        $amountCast = Money::of($paymentTotal, $paymentCurrency->value);

        abort_if($paymentTotal > 15000, 400);

        $order = $user->orders()->create([
            'user_id' => $user->id,
            'amount' => $amountCast,

            'metadata' => [
                'cart_uuid' => $cartUuid,
            ],
        ]);

        $orderItems = $order->items()->createMany($cartItems);

        $orderMessages = $order->messages()->create(
            [
                'user_id' => null,
                'message' => 'Twoje zamówienie zostało przyjęte! Jeden z naszych adminów już się nim zajmuje – realizacja zazwyczaj do 20 minut, maksymalnie 12h. Dziękujemy za zakupy w Dzikshop!',
            ],
        );

        $orderPayment = $order->payments()->create([
            'user_id' => $user->id,
            'amount' => $amountCast,
            'method' => $method,
        ]);

        $hotpay = new HotPayService($method);

        $hotpayPayment = $hotpay->generatePayment(
            amount: $paymentTotal,
            description: "Zamówienie {$order->uuid}",
            redirectUrl: route('orders.show', $order),
            orderId: $orderPayment->uuid,
            email: $user->email,
            personalData: $user->name,
        );

        return Inertia::location($hotpayPayment['URL']);
    }

    private function getCurrencies()
    {
        return Cache::remember(
            key: 'currencies',
            ttl: now()->addHour(),
            callback: function () {
                if (function_exists('Swoole\Coroutine\run') && \Swoole\Coroutine::getCid() !== -1) {
                    // Running inside a Swoole coroutine context
                    $url = parse_url('https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
                    $client = new Client($url['host'], 443, true);
                    $client->set(['timeout' => 5]);
                    $client->get($url['path']);
                    $body = $client->body;
                    $client->close();
                } else {
                    // Fallback for non-Swoole environment (php artisan serve, or CLI)
                    $response = Http::get('https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
                    $body = $response->body();
                }

                $xml = simplexml_load_string($body);
                $currencies = [];

                foreach ($xml->Cube->Cube->Cube as $cube) {
                    $currency = (string)$cube['currency'];
                    $rate = (float)$cube['rate'];
                    $currencies[$currency] = $rate;
                }

                return $currencies;
            }
        );
    }
}
