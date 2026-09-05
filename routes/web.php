<?php

use App\Http\Controllers\ActivationWizardController;
use App\Http\Controllers\Admin\AdminActivityLogController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\Admin\CouponAdminController;
use App\Http\Controllers\Admin\LicenseAdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalSettingsController;
use App\Http\Controllers\Portal\PortalTicketController;
use App\Http\Controllers\PricingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::post('/pricing/{plan}/subscribe', [PricingController::class, 'subscribe'])->name('pricing.subscribe');

Route::middleware('anubis.customer')->group(function () {
    Route::get('/activate', [ActivationWizardController::class, 'show'])->name('activate');
    Route::post('/activate', [ActivationWizardController::class, 'confirm'])->middleware('throttle:10,1')->name('activate.confirm');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('anubis.admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/activity-log', [AdminActivityLogController::class, 'index'])->name('activity-log');

        Route::get('/licenses', [LicenseAdminController::class, 'index'])->name('licenses.index');
        Route::get('/licenses/create', [LicenseAdminController::class, 'create'])->name('licenses.create');
        Route::get('/licenses/export', [LicenseAdminController::class, 'export'])->name('licenses.export');
        Route::post('/licenses/bulk-action', [LicenseAdminController::class, 'bulkAction'])->name('licenses.bulk-action');
        Route::post('/licenses', [LicenseAdminController::class, 'store'])->name('licenses.store');
        Route::post('/licenses/{license}/cancel', [LicenseAdminController::class, 'cancel'])->name('licenses.cancel');
        Route::post('/licenses/{license}/reactivate', [LicenseAdminController::class, 'reactivate'])->name('licenses.reactivate');
        Route::post('/licenses/{license}/extend', [LicenseAdminController::class, 'extend'])->name('licenses.extend');
        Route::post('/licenses/{license}/delete', [LicenseAdminController::class, 'destroy'])->name('licenses.destroy');
        Route::get('/licenses/{license}/activity', [LicenseAdminController::class, 'activity'])->name('licenses.activity');
        Route::get('/licenses/{license}/invoice', [InvoiceController::class, 'generate'])->name('licenses.invoice');
        Route::post('/licenses/{license}/domain/approve', [LicenseAdminController::class, 'approveDomainChange'])->name('licenses.domain.approve');
        Route::post('/licenses/{license}/domain/reject', [LicenseAdminController::class, 'rejectDomainChange'])->name('licenses.domain.reject');
        Route::post('/licenses/{license}/plan/approve', [LicenseAdminController::class, 'approvePlanChange'])->name('licenses.plan.approve');
        Route::post('/licenses/{license}/plan/reject', [LicenseAdminController::class, 'rejectPlanChange'])->name('licenses.plan.reject');
        Route::post('/licenses/{license}/reset-fingerprint', [LicenseAdminController::class, 'resetFingerprint'])->name('licenses.reset-fingerprint');

        Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');
        Route::post('/customers/{customer}/notes', [AdminCustomerController::class, 'updateNotes'])->name('customers.notes');
        Route::post('/customers/{customer}/reset-password', [AdminCustomerController::class, 'resetPassword'])->name('customers.reset-password');
        Route::post('/customers/{customer}/delete', [AdminCustomerController::class, 'destroy'])->name('customers.destroy');

        Route::get('/coupons', [CouponAdminController::class, 'index'])->name('coupons.index');
        Route::get('/coupons/create', [CouponAdminController::class, 'create'])->name('coupons.create');
        Route::post('/coupons', [CouponAdminController::class, 'store'])->name('coupons.store');
        Route::post('/coupons/{coupon}/toggle', [CouponAdminController::class, 'toggle'])->name('coupons.toggle');
        Route::post('/coupons/{coupon}/delete', [CouponAdminController::class, 'destroy'])->name('coupons.destroy');

        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/reply', [AdminTicketController::class, 'reply'])->name('tickets.reply');
        Route::post('/tickets/{ticket}/close', [AdminTicketController::class, 'close'])->name('tickets.close');
        Route::post('/tickets/{ticket}/reopen', [AdminTicketController::class, 'reopen'])->name('tickets.reopen');

        // Admin account management — super admins only.
        Route::middleware('anubis.super-admin')->group(function () {
            Route::get('/admins', [AdminManagementController::class, 'index'])->name('admins.index');
            Route::get('/admins/create', [AdminManagementController::class, 'create'])->name('admins.create');
            Route::post('/admins', [AdminManagementController::class, 'store'])->name('admins.store');
            Route::post('/admins/{admin}/delete', [AdminManagementController::class, 'destroy'])->name('admins.destroy');
        });
    });
});

Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/login', [PortalAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [PortalAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
    Route::get('/register', [PortalAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [PortalAuthController::class, 'register'])->middleware('throttle:5,1')->name('register.submit');
    Route::post('/logout', [PortalAuthController::class, 'logout'])->name('logout');

    Route::middleware('anubis.customer')->group(function () {
        Route::get('/dashboard', [PortalDashboardController::class, 'index'])->name('dashboard');
        Route::get('/licenses/{license}/get-started', [PortalDashboardController::class, 'getStarted'])->name('licenses.get-started');
        Route::post('/licenses/{license}/renew', [PortalDashboardController::class, 'renew'])->name('licenses.renew');
        Route::get('/licenses/{license}/invoice', [PortalDashboardController::class, 'invoice'])->name('licenses.invoice');
        Route::post('/licenses/{license}/request-domain-change', [PortalSettingsController::class, 'requestDomainChange'])->name('licenses.request-domain-change');
        Route::post('/licenses/{license}/request-plan-change', [PortalSettingsController::class, 'requestPlanChange'])->name('licenses.request-plan-change');
        Route::get('/settings', [PortalSettingsController::class, 'edit'])->name('settings');
        Route::post('/settings/name', [PortalSettingsController::class, 'updateName'])->name('settings.name');
        Route::post('/settings/password', [PortalSettingsController::class, 'updatePassword'])->name('settings.password');

        Route::get('/tickets', [PortalTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [PortalTicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [PortalTicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [PortalTicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/reply', [PortalTicketController::class, 'reply'])->name('tickets.reply');
    });
});
