<?php

use Inertia\Inertia;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DossierUserController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/create-user', [DossierUserController::class, 'create'])->name('create-user');
Volt::route('/edit-user/{user}', 'edit-user')->name('edit.user');
