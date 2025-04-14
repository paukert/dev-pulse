<?php

declare(strict_types=1);

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(static function (): void {
    Route::get('users', [UserController::class, 'index'])
        ->name('users.index');

    Route::get('users/{id}/edit', [UserController::class, 'edit'])
        ->name('users.edit');

    Route::patch('users/{id}', [UserController::class, 'update'])
        ->name('users.update');

    Route::delete('users', [UserController::class, 'destroy'])
        ->name('users.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
