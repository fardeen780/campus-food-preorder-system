<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController;
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
    });

Route::middleware(['auth', EnsureRole::class.':staff'])
    ->prefix('staff')->name('staff.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'staff'])->name('dashboard');
    });

Route::middleware(['auth', EnsureRole::class.':manager'])
    ->prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'manager'])->name('dashboard');
    });

Route::middleware(['auth', EnsureRole::class.':admin'])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    });