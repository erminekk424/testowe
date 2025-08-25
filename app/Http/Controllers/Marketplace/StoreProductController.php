<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductDetailsResource;
use App\Models\Product;

class StoreProductController extends Controller
{
    public function show(Product $product)
    {
        return $product->toResource(ProductDetailsResource::class);
    }
}
