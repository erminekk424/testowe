<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartAddRequest;
use App\Models\Product;
use Cart;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function index()
    {
        if (! $cartUuid = Session::get('cart_uuid')) {
            $cartUuid = Str::uuid();
            Session::put('cart_uuid', $cartUuid);
        }
        Cart::session($cartUuid);

        return response()->json([
            'items' => Cart::getContent()->select(['id', 'name', 'price', 'quantity'])->values(),
        ]);
    }

    public function store(CartAddRequest $request)
    {
        $data = $request->safe(['product', 'quantity']);

        //       TODO: cache!
        $product = Product::whereUuid($data['product'])->firstOrFail();

        //       TODO: make it DRY!
        if (! $cartUuid = Session::get('cart_uuid')) {
            $cartUuid = Str::uuid();
            Session::put('cart_uuid', $cartUuid);
        }
        Cart::session($cartUuid);

        $quantity = $data['quantity'];

        //       TODO: offload this to different methods!
        if ($quantity === 0) {
            Cart::remove($product->uuid);
        } else {
            if (Cart::has($product->uuid)) {
                Cart::update($product->uuid, [
                    'quantity' => [
                        'relative' => false,
                        'value' => Number::clamp($quantity, $product->quantity->min, $product->quantity->max),
                    ],
                ]);
            } else {
                Cart::add([
                    'id' => $product->uuid,
                    'name' => $product->name,
                    'price' => $product->price->getAmount()->toFloat(),
                    'quantity' => Number::clamp($quantity, $product->quantity->min, $product->quantity->max),
                    'attributes' => [],
                    'associatedModel' => $product,
                ]);
            }
        }

        return response()->json([
            'items' => Cart::getContent()->select(['id', 'name', 'price', 'quantity'])->values(),
        ]);
    }

    public function destroy()
    {
        //       TODO: make it DRY!
        if (! $cartUuid = Session::get('cart_uuid')) {
            $cartUuid = Str::uuid();
            Session::put('cart_uuid', $cartUuid);
        }
        Cart::session($cartUuid);
        Cart::clear();

        return response(status: 200);
    }
}
