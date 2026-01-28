<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VcsInstanceUserController;
use Illuminate\Support\Facades\Route;

$hasAdminRoleMiddleware = 'role:' . UserRole::ADMIN->value;

Route::redirect('/', 'dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(static function (): void {
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('users/search', [UserController::class, 'search'])
        ->name('users.search');
});

Route::middleware(['auth', 'verified', $hasAdminRoleMiddleware])->group(static function (): void {
    Route::get('users', [UserController::class, 'index'])
        ->name('users.index');

    Route::get('users/{id}/edit', [UserController::class, 'edit'])
        ->name('users.edit');

    Route::patch('users/{id}', [UserController::class, 'update'])
        ->name('users.update');

    Route::delete('users', [UserController::class, 'destroy'])
        ->name('users.destroy');

    Route::get('vcs-instance-users/search', [VcsInstanceUserController::class, 'search'])
        ->name('vcs-instance-users.search');

    Route::get('repositories/search', [RepositoryController::class, 'search'])
        ->name('repositories.search');

    Route::resource('repositories', RepositoryController::class)->only([
        'index',
        'create',
        'store',
        'edit',
        'update',
        'destroy',
    ]);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
