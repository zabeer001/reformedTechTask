<?php

use App\Http\Controllers\Frontend\FrontendAuthController;
use App\Http\Controllers\Frontend\FrontendDashbaordController;
use App\Http\Controllers\Frontend\FrontendEmployeeController;
use App\Http\Controllers\Frontend\FrontendUserController;
use Illuminate\Support\Facades\Route;


Route::get('/', [FrontendAuthController::class, 'signin'])->name('login');
Route::get('/dashboard', [FrontendDashbaordController::class, 'dashboard'])->name('dashboard');


Route::get('/dashboard/employees', [FrontendEmployeeController::class, 'index'])
    ->name('employees.index');


Route::get('/dashboard/employees/create', [FrontendEmployeeController::class, 'create'])
    ->name('employees.create');


Route::get('/dashboard/employees/{employee}/edit', [FrontendEmployeeController::class, 'edit'])
    ->name('employees.edit');






Route::get('/dashboard/users', [FrontendUserController::class, 'index'])
    ->name('users.index');


Route::get('/dashboard/users/create', [FrontendUserController::class, 'create'])
    ->name('users.create');


Route::get('/dashboard/users/{employee}/edit', [FrontendUserController::class, 'edit'])
    ->name('users.edit');

