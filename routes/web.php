<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
Route::get('/import-logs', [CompanyController::class, 'importLogs'])->name('companies.import-logs');
Route::delete('/import-logs', [CompanyController::class, 'bulkDestroyImportLogs'])->name('companies.import-logs.bulk-destroy');
Route::delete('/import-logs/clear', [CompanyController::class, 'clearImportLogs'])->name('companies.import-logs.clear');
Route::delete('/import-logs/{importLog}', [CompanyController::class, 'destroyImportLog'])->name('companies.import-logs.destroy');
Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
Route::post('/companies/import-places', [CompanyController::class, 'importPlaces'])->name('companies.import-places');
Route::patch('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
Route::delete('/companies', [CompanyController::class, 'bulkDestroy'])->name('companies.bulk-destroy');

Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
Route::get('/leads/export-csv', [LeadController::class, 'exportCsv'])->name('leads.export-csv');
Route::patch('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status');

Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');
Route::post('/settings/logo', [SettingsController::class, 'uploadLogo'])->name('settings.upload-logo');
Route::post('/settings/login-logo', [SettingsController::class, 'uploadLoginLogo'])->name('settings.upload-login-logo');
Route::post('/settings/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
Route::patch('/settings/users/{user}', [SettingsController::class, 'updateUser'])->name('settings.users.update');
Route::delete('/settings/users/{user}', [SettingsController::class, 'destroyUser'])->name('settings.users.destroy');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});
