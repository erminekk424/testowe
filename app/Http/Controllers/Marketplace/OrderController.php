<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $order = $this->getUserOrdersList($request->user())->first();

        abort_if(!$order, 404);

        return redirect()->route('orders.show', ['order' => $order]);
    }

    public function show(Request $request, Order $order)
    {
        $user = $request->user();
        abort_if($user->id !== $order->user_id, 404);

        return Inertia::render('dashboard/orders/show', [
            'order' => Inertia::defer(fn() => $this->getOrderData($order)),
            'orders' => Inertia::defer(fn() => $this->getUserOrdersList($user))->merge()->matchOn('uuid'),
        ]);
    }

    private function getOrderData(Order $order)
    {
        return Cache::remember(
            key: "order:{$order->id}",
            ttl: now()->addSeconds(60),
            callback: fn() => $order->load(['items', 'payments'])->toResource(OrderResource::class)
        );
    }

    private function getUserOrdersList(User $user)
    {
        return Cache::remember(
                key: "user-orders:{$user->id}",
                ttl: now()->addSeconds(30),
                callback: fn() => $user->orders()->latest('id')->get()->toResourceCollection()
            );
    }
}
