<?php
//
//use App\Models\Product;
//use Illuminate\Foundation\Inspiring;
//use Illuminate\Support\Arr;
//use Illuminate\Support\Facades\Artisan;
//
////
//// Artisan::command('inspire', function () {
////    $this->comment(Inspiring::quote());
//// })->purpose('Display an inspiring quote');
//
//Artisan::command('products:set-rarity', function () {
//    $products = Product::all();
//
//    $rarites = [
//        'divine',
//        'mythical',
//        'legendary',
//        'rare',
//        'uncommon',
//        'common',
//    ];
//    function generateVibrantColor()
//    {
//        $hue = floor(random_int(0, 360));
//        $saturation = 85;
//        $lightness = 65;
//
//        return "hsl($hue, $saturation%, $lightness%)";
//    }
//
//    DB::beginTransaction();
//    $products->each(function (Product $product) use ($rarites) {
//        $randomRarity = Arr::random($rarites);
//        $randomColor = generateVibrantColor();
//
//        $product->update([
//            'attributes->rarity' => $randomRarity,
//            'attributes->color' => $randomColor,
//        ]);
//    });
//    DB::commit();
//});
