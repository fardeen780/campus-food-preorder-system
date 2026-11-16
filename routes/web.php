<?php

use App\Http\Controllers\AdminActivityController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagerCategoryController;
use App\Http\Controllers\ManagerFoodController;
use App\Http\Controllers\ManagerOrderController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StaffOrderController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\TransferController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', EnsureRole::class.':student'])
    ->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'student'])->name('dashboard');

        Route::get('/menu', [MenuController::class, 'index'])->name('menu');

        Route::get('/cart', [CartController::class, 'index'])->name('cart');
        Route::post('/cart', [CartController::class, 'store'])->name('cart.add');
        Route::patch('/cart/{id}', [CartController::class, 'update'])->whereNumber('id')->name('cart.update');
        Route::delete('/cart/{id}', [CartController::class, 'destroy'])->whereNumber('id')->name('cart.remove');

        Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
        Route::post('/checkout', [CheckoutController::class, 'store'])
            ->middleware('throttle:10,1')->name('checkout.place');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->whereNumber('id')->name('orders.show');
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->whereNumber('id')->name('orders.cancel');

        Route::get('/tokens', [TokenController::class, 'index'])->name('tokens');
        Route::get('/tokens/{id}/transfer', [TokenController::class, 'create'])->whereNumber('id')->name('tokens.transfer');
        Route::post('/tokens/{id}/transfer', [TokenController::class, 'store'])
            ->whereNumber('id')->middleware('throttle:10,1')->name('tokens.transfer.send');

        Route::get('/transfers/sent', [TransferController::class, 'sent'])->name('transfers.sent');
        Route::get('/transfers/received', [TransferController::class, 'received'])->name('transfers.received');
        Route::post('/transfers/{id}/accept', [TransferController::class, 'accept'])->whereNumber('id')->name('transfers.accept');
        Route::post('/transfers/{id}/reject', [TransferController::class, 'reject'])->whereNumber('id')->name('transfers.reject');
        Route::post('/transfers/{id}/cancel', [TransferController::class, 'cancel'])->whereNumber('id')->name('transfers.cancel');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('/notifications/{id}/open', [NotificationController::class, 'open'])->whereUuid('id')->name('notifications.open');
    });

Route::middleware(['auth', EnsureRole::class.':staff'])
    ->prefix('staff')->name('staff.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'staff'])->name('dashboard');

        Route::get('/orders', [StaffOrderController::class, 'index'])->name('orders');
        Route::get('/orders/{id}', [StaffOrderController::class, 'show'])->whereNumber('id')->name('orders.show');
        Route::post('/orders/{id}/advance', [StaffOrderController::class, 'advance'])->whereNumber('id')->name('orders.advance');
        Route::post('/orders/{id}/collect', [StaffOrderController::class, 'collect'])
            ->whereNumber('id')->middleware('throttle:30,1')->name('orders.collect');
        Route::post('/orders/{id}/payment', [StaffOrderController::class, 'pay'])
            ->whereNumber('id')->middleware('throttle:30,1')->name('orders.pay');

        Route::get('/verify', [StaffOrderController::class, 'verifyForm'])->name('verify');
        Route::post('/verify', [StaffOrderController::class, 'verify'])
            ->middleware('throttle:30,1')->name('verify.lookup');
    });

Route::middleware(['auth', EnsureRole::class.':manager'])
    ->prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'manager'])->name('dashboard');

        Route::get('/foods', [ManagerFoodController::class, 'index'])->name('foods');
        Route::get('/foods/create', [ManagerFoodController::class, 'create'])->name('foods.create');
        Route::post('/foods', [ManagerFoodController::class, 'store'])->name('foods.store');
        Route::get('/foods/{id}/edit', [ManagerFoodController::class, 'edit'])->whereNumber('id')->name('foods.edit');
        Route::put('/foods/{id}', [ManagerFoodController::class, 'update'])->whereNumber('id')->name('foods.update');
        Route::post('/foods/{id}/toggle', [ManagerFoodController::class, 'toggle'])->whereNumber('id')->name('foods.toggle');
        Route::delete('/foods/{id}', [ManagerFoodController::class, 'destroy'])->whereNumber('id')->name('foods.destroy');
        Route::post('/foods/{id}/restore', [ManagerFoodController::class, 'restore'])->whereNumber('id')->name('foods.restore');

        Route::get('/categories', [ManagerCategoryController::class, 'index'])->name('categories');
        Route::post('/categories', [ManagerCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{id}', [ManagerCategoryController::class, 'update'])->whereNumber('id')->name('categories.update');

        Route::get('/orders', [ManagerOrderController::class, 'index'])->name('orders');
        Route::get('/orders/{id}', [ManagerOrderController::class, 'show'])->whereNumber('id')->name('orders.show');

        Route::get('/dues', [ManagerOrderController::class, 'dues'])->name('dues');
        Route::get('/dues/{id}', [ManagerOrderController::class, 'studentDues'])->whereNumber('id')->name('dues.student');
    });

Route::middleware(['auth', EnsureRole::class.':admin'])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [AdminUserController::class, 'edit'])->whereNumber('id')->name('users.edit');
        Route::put('/users/{id}', [AdminUserController::class, 'update'])->whereNumber('id')->name('users.update');

        Route::get('/activity', [AdminActivityController::class, 'index'])->name('activity');
    });