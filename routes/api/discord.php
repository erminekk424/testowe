<?php

use App\Enums\PaymentStatus;
use App\Http\Requests\DiscordOrdersRequest;
use App\Http\Resources\DiscordOrderResource;
use App\Http\Resources\DiscordProductResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/users/{discordId}/info', function (string $discordId) {
    $user = User::with(['orders', 'payments'])->where('discord_id', $discordId)->first();

    return response()->json([
        'success' => true,
        'user' => $user,
    ]);
})->where('discordId', '[0-9]+');

Route::get('/orders', function () {
    $orders = Order::withWhereHas('payments', function ($query) {
        $query->where('status', PaymentStatus::Success);
    })->get();

    return response()->json([
        'success' => true,
        'data' => $orders->toResourceCollection(DiscordOrderResource::class),
    ]);
});

Route::post('/orders', function (DiscordOrdersRequest $request) {
    $orders = $request->only('orders');

    return response()->json([
        'success' => true,
        'data' => $orders,
    ]);
});

Route::get('/products', function () {
    $products = Product::paginate(perPage: 2);

    return $products->toResourceCollection(DiscordProductResource::class);
});

Route::fallback(fn () => response()->json(['message' => 'Not Found'], 404));
