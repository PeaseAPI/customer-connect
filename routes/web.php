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
        Route::post('/leads', [CrmController::class, 'storeLead'])->name('leads.store');
        Route::put('/leads/{id}', [CrmController::class, 'updateLead'])->name('leads.update');
        Route::delete('/leads/{id}', [CrmController::class, 'destroyLead'])->name('leads.destroy');
        Route::post('/leads/{id}/convert', [CrmController::class, 'convertLead'])->name('leads.convert');
        Route::get('/clients', [CrmController::class, 'clients'])->name('clients');
        Route::post('/clients', [CrmController::class, 'storeClient'])->name('clients.store');
        Route::put('/clients/{id}', [CrmController::class, 'updateClient'])->name('clients.update');
        Route::delete('/clients/{id}', [CrmController::class, 'destroyClient'])->name('clients.destroy');
        Route::get('/deals', [CrmController::class, 'deals'])->name('deals');
        Route::post('/deals', [CrmController::class, 'storeDeal'])->name('deals.store');
        Route::put('/deals/{id}', [CrmController::class, 'updateDeal'])->name('deals.update');
        Route::delete('/deals/{id}', [CrmController::class, 'destroyDeal'])->name('deals.destroy');
        Route::get('/pipelines', [CrmController::class, 'pipelines'])->name('pipelines');
        Route::post('/pipelines', [CrmController::class, 'storePipeline'])->name('pipelines.store');
        Route::put('/pipelines/{id}', [CrmController::class, 'updatePipeline'])->name('pipelines.update');
        Route::delete('/pipelines/{id}', [CrmController::class, 'destroyPipeline'])->name('pipelines.destroy');
    });

    // Projects
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{id}', [ProjectController::class, 'show'])->name('show');
        Route::put('/{id}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('destroy');
        Route::get('/{projectId}/tasks', [ProjectController::class, 'tasks'])->name('tasks');
    });
    Route::get('/tasks', [ProjectController::class, 'allTasks'])->name('tasks.index');
    Route::post('/tasks', [ProjectController::class, 'storeTask'])->name('tasks.store');
    Route::put('/tasks/{id}', [ProjectController::class, 'updateTask'])->name('tasks.update');
    Route::delete('/tasks/{id}', [ProjectController::class, 'destroyTask'])->name('tasks.destroy');

        // Finance
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/invoices', [FinanceController::class, 'invoices'])->name('invoices');
        Route::post('/invoices', [FinanceController::class, 'storeInvoice'])->name('invoices.store');
        Route::put('/invoices/{id}', [FinanceController::class, 'updateInvoice'])->name('invoices.update');
        Route::delete('/invoices/{id}', [FinanceController::class, 'destroyInvoice'])->name('invoices.destroy');
        Route::get('/estimates', [FinanceController::class, 'estimates'])->name('estimates');
        Route::post('/estimates', [FinanceController::class, 'storeEstimate'])->name('estimates.store');
        Route::put('/estimates/{id}', [FinanceController::class, 'updateEstimate'])->name('estimates.update');
        Route::delete('/estimates/{id}', [FinanceController::class, 'destroyEstimate'])->name('estimates.destroy');
        Route::get('/payments', [FinanceController::class, 'payments'])->name('payments');
        Route::post('/payments', [FinanceController::class, 'storePayment'])->name('payments.store');
        Route::get('/expenses', [FinanceController::class, 'expenses'])->name('expenses');
        Route::post('/expenses', [FinanceController::class, 'storeExpense'])->name('expenses.store');
        Route::put('/expenses/{id}', [FinanceController::class, 'updateExpense'])->name('expenses.update');
        Route::delete('/expenses/{id}', [FinanceController::class, 'destroyExpense'])->name('expenses.destroy');
        Route::post('/expenses/{id}/approve', [FinanceController::class, 'approveExpense'])->name('expenses.approve');
    });

        // HRM
    Route::prefix('hrm')->name('hrm.')->group(function () {
        Route::get('/employees', [HrmController::class, 'employees'])->name('employees');
        Route::post('/employees', [HrmController::class, 'storeEmployee'])->name('employees.store');
        Route::put('/employees/{id}', [HrmController::class, 'updateEmployee'])->name('employees.update');
        Route::delete('/employees/{id}', [HrmController::class, 'destroyEmployee'])->name('employees.destroy');
        Route::get('/attendance', [HrmController::class, 'attendance'])->name('attendance');
        Route::post('/attendance', [HrmController::class, 'storeAttendance'])->name('attendance.store');
        Route::put('/attendance/{id}', [HrmController::class, 'updateAttendance'])->name('attendance.update');
        Route::delete('/attendance/{id}', [HrmController::class, 'destroyAttendance'])->name('attendance.destroy');
        Route::get('/leaves', [HrmController::class, 'leaves'])->name('leaves');
        Route::post('/leaves', [HrmController::class, 'storeLeave'])->name('leaves.store');
        Route::put('/leaves/{id}', [HrmController::class, 'updateLeave'])->name('leaves.update');
        Route::delete('/leaves/{id}', [HrmController::class, 'destroyLeave'])->name('leaves.destroy');
    });
    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::get('/company', [SettingController::class, 'company'])->name('company');
        Route::put('/company', [SettingController::class, 'updateCompany'])->name('company.update');
        Route::get('/notifications', [SettingController::class, 'notifications'])->name('notifications');
    });
});

