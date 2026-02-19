<?php

use Inertia\Inertia;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DossierUserController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/users', [DossierUserController::class, 'index'])->name('users');
Route::get('/users/create', [DossierUserController::class, 'create'])->name('create-user');
Route::get('/users/edit/{user}', [DossierUserController::class, 'edit'])->name('edit-user');
Route::get('/users/show/{user}', [DossierUserController::class, 'show'])->name('show-user');
