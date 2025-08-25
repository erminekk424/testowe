<?php

use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\Marketplace\CartController;
use App\Http\Controllers\Marketplace\CheckoutController;
use App\Http\Controllers\Marketplace\GlobalSearchController;
use App\Http\Controllers\Marketplace\OrderController;
use App\Http\Controllers\Marketplace\OrderMessageController;
use App\Http\Controllers\Marketplace\StoreController;
use App\Http\Controllers\Marketplace\StoreProductController;
//use App\Http\Controllers\Marketplace\StoreSearchController;
use App\Http\Controllers\ParentsController;
use Illuminate\Support\Facades\Route;

// TODO: DOKOŃCZYĆ LANDINGI DLA RODZICÓW ORAZ INFORMACJI!
// TODO: OGARNAC RESPONSYWNOSC JEBANEGO MARKETPLACE!
// TODO: SORTOWANIE PRODUKTÓW (OD CENY, RZADKOŚCI, POPULARNOŚCI)!
// TODO: GLOBAL SEARCH MODAL!
// TODO: CHECKOUT DOKOŃCZYĆ NA 100% + WYSYŁKA WIADOMOŚCI DO ZAMÓWIENIA!
// TODO: PRZY WEJŚCIU NA MARKETPLACE ŁADOWAĆ ZAWARTOŚĆ KOSZYKA!
// TODO: DODAĆ KODY RABATOWE NA CAŁY KOSZYK!
// TODO: ZACZĄĆ NAPIERDALĄĆ FILAMENTPHP!

/*
|--------------------------------------------------------------------------|
|                                                                          |
|                                 G U E S T                                |
|                                                                          |
|--------------------------------------------------------------------------|
*/

// Landings
Route::get('/', HomeController::class)->name('home');
Route::get('/dla-rodzicow', ParentsController::class)->name('parents');
Route::get('/informacje', InfoController::class)->name('info');
Route::get('/najczestsze-pytania', FaqController::class)->name('faq');

// Marketplace
Route::prefix('/marketplace')->name('marketplace.')->group(function () {
    Route::get('/', [StoreController::class, 'redirect'])->name('index');

    // Marketplace API
    Route::get('/product/{product}', [StoreProductController::class, 'show'])->name('products.show');

    Route::get('/{game}/{productType}', [StoreController::class, 'show'])->name('show');
    //    Route::get('/{game}/{productType}/search', StoreSearchController::class)->name('search');

});

// Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// Global Search API
Route::get('/search', GlobalSearchController::class)->name('search');

// Cart API
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::delete('/cart', [CartController::class, 'destroy'])->name('cart.destroy');

/*
|--------------------------------------------------------------------------|
|                                                                          |
|                        A U T H E N T I C A T E D                         |
|                                                                          |
|--------------------------------------------------------------------------|
*/

// Verified
Route::middleware(['auth', 'verified'])->group(function () {

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Orders API
    Route::get('/orders/{order}/messages', [OrderMessageController::class, 'show'])->name('orders.messages.show');
    Route::post('/orders/{order}/messages', [OrderMessageController::class, 'store'])->name('orders.messages.store');

});

// Unverified
require __DIR__.'/settings.php';

// Authentication
require __DIR__.'/auth.php';
