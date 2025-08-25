<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __invoke()
    {
        $products = Cache::remember(
            key: 'home:products',
            ttl: 60,
            callback: function () {
                return Product::inRandomOrder()->take(5)->get()->toResourceCollection();
            }
        );

        return Inertia::render('home', [
            'trendingProducts' => Inertia::defer(fn() => $products),
            'lastPurchased' => Inertia::defer(fn() => $products),
        ]);
    }
}
