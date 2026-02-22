<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoProjectController;
use App\Http\Controllers\FollowUpController;
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
Route::patch('/companies/{company}/demo-prompt', [CompanyController::class, 'updateDemoPrompt'])->name('companies.update-demo-prompt');
Route::post('/companies/import-places', [CompanyController::class, 'importPlaces'])->name('companies.import-places');
Route::patch('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
Route::delete('/companies', [CompanyController::class, 'bulkDestroy'])->name('companies.bulk-destroy');
Route::post('/companies/{company}/demo-projects', [DemoProjectController::class, 'generate'])->name('companies.demo-projects.generate');
Route::post('/companies/{company}/demo-projects/start', [DemoProjectController::class, 'start'])->name('companies.demo-projects.start');
Route::post('/demo-projects/{demoProject}/run', [DemoProjectController::class, 'run'])->name('demo-projects.run');
Route::get('/demo-projects/{demoProject}/progress', [DemoProjectController::class, 'progress'])->name('demo-projects.progress');
Route::get('/demo-projects/{demoProject}/download', [DemoProjectController::class, 'download'])->name('demo-projects.download');

Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
Route::get('/leads/export-csv', [LeadController::class, 'exportCsv'])->name('leads.export-csv');
Route::patch('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status');
Route::patch('/leads/{lead}/quick-status', [LeadController::class, 'quickStatus'])->name('leads.quick-status');
Route::post('/leads/{lead}/send-email', [LeadController::class, 'sendEmail'])->name('leads.send-email');

Route::get('/follow-ups', [FollowUpController::class, 'index'])->name('follow-ups.index');
Route::patch('/follow-ups/{followUp}/status', [FollowUpController::class, 'updateStatus'])->name('follow-ups.update-status');
Route::patch('/follow-ups/{followUp}/called', [FollowUpController::class, 'markCalled'])->name('follow-ups.mark-called');

Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');
Route::post('/settings/logo', [SettingsController::class, 'uploadLogo'])->name('settings.upload-logo');
Route::post('/settings/login-logo', [SettingsController::class, 'uploadLoginLogo'])->name('settings.upload-login-logo');
Route::post('/settings/test-cloud-opus', [SettingsController::class, 'testCloudOpus'])->name('settings.test-cloud-opus');
Route::post('/settings/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
Route::patch('/settings/users/{user}', [SettingsController::class, 'updateUser'])->name('settings.users.update');
Route::delete('/settings/users/{user}', [SettingsController::class, 'destroyUser'])->name('settings.users.destroy');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});
