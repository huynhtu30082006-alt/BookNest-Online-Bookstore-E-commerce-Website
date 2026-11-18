<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;

// Admin Controllers
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController; // <- Thêm import DashboardController

use Illuminate\Support\Facades\Route;

// Storefront (placeholder until the product-browsing pages are merged)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Registration / login (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');
});

// Member area
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
});

// Cart (guests and members)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/items/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{product}', [CartController::class, 'destroy'])->name('cart.remove');
Route::post('/cart/coupon', [CouponController::class, 'apply'])->name('coupon.apply');
Route::delete('/cart/coupon', [CouponController::class, 'remove'])->name('coupon.remove');

// Checkout (guests and members)
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// Order confirmation + customer-side order status (guest lookup defined before {order})
Route::get('/orders/track', [OrderController::class, 'trackForm'])->name('orders.track');
Route::post('/orders/track', [OrderController::class, 'track'])->middleware('throttle:10,1')->name('orders.track.submit');
Route::get('/orders/{order}/confirmation', [OrderController::class, 'confirmation'])->whereNumber('order')->name('orders.confirmation');
Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');
Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->whereNumber('order')->name('orders.cancel');

// ADMIN MANAGEMENT MODULES
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard'); // <- Thêm route Dashboard
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::resource('orders', AdminOrderController::class);
        Route::resource('coupons', AdminCouponController::class);
        Route::resource('customers', AdminCustomerController::class);
    });