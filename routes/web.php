<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\CrmController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\FinanceController;
use App\Http\Controllers\Web\HrmController;
use App\Http\Controllers\Web\SettingController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password reset
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Email verification
Route::get('/verify-email', [AuthController::class, 'showVerifyEmail'])->name('verification.notice');
Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CRM
    Route::prefix('crm')->name('crm.')->group(function () {
        Route::get('/leads', [CrmController::class, 'leads'])->name('leads');
        Route::get('/clients', [CrmController::class, 'clients'])->name('clients');
        Route::get('/deals', [CrmController::class, 'deals'])->name('deals');
        Route::get('/pipelines', [CrmController::class, 'pipelines'])->name('pipelines');
    });

    // Projects
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('/{id}', [ProjectController::class, 'show'])->name('show');
        Route::get('/{projectId}/tasks', [ProjectController::class, 'tasks'])->name('tasks');
    });

    // Tasks (standalone)
    Route::get('/tasks', [ProjectController::class, 'allTasks'])->name('tasks.index');

    // Finance
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/invoices', [FinanceController::class, 'invoices'])->name('invoices');
        Route::get('/estimates', [FinanceController::class, 'estimates'])->name('estimates');
        Route::get('/payments', [FinanceController::class, 'payments'])->name('payments');
        Route::get('/expenses', [FinanceController::class, 'expenses'])->name('expenses');
    });

    // HRM
    Route::prefix('hrm')->name('hrm.')->group(function () {
        Route::get('/employees', [HrmController::class, 'employees'])->name('employees');
        Route::get('/attendance', [HrmController::class, 'attendance'])->name('attendance');
        Route::get('/leaves', [HrmController::class, 'leaves'])->name('leaves');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::get('/company', [SettingController::class, 'company'])->name('company');
        Route::get('/notifications', [SettingController::class, 'notifications'])->name('notifications');
    });
});

