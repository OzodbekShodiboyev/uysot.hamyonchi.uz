<?php

use App\Http\Controllers\{
    ApartmentController,
    BlockController,
    ClientController,
    ContractController,
    DashboardController,
    LeadController,
    PaymentController,
    ReportController,
};
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Laravel 10
|--------------------------------------------------------------------------
*/

// ─── Autentifikatsiya ─────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',   [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login',  [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
     ->middleware('auth')
     ->name('logout');

// ─── Himoyalangan sahifalar ───────────────────────────────────────
Route::middleware(['auth', 'active'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ─── Bloklar CRUD ─────────────────────────────────────
    Route::prefix('blocks')->name('blocks.')->group(function () {
        Route::get('/',               [BlockController::class, 'index'])->name('index');
        Route::post('/',              [BlockController::class, 'store'])->name('store');
        Route::put('/{block}',        [BlockController::class, 'update'])->name('update');
        Route::delete('/{block}',     [BlockController::class, 'destroy'])->name('destroy');
        Route::post('/{block}/toggle-active', [BlockController::class, 'toggleActive'])->name('toggleActive');
    });

    // ─── Xonadonlar ───────────────────────────────────────
    Route::prefix('apartments')->name('apartments.')->group(function () {
        Route::get('/',                           [ApartmentController::class, 'index'])->name('index');
        Route::get('/block/{block}',              [ApartmentController::class, 'block'])->name('block');
        Route::post('/',                          [ApartmentController::class, 'store'])->name('store');
        Route::post('/bulk',                      [ApartmentController::class, 'bulkStore'])->name('bulk');
        Route::put('/{apartment}',                [ApartmentController::class, 'update'])->name('update');
        Route::delete('/{apartment}',             [ApartmentController::class, 'destroy'])->name('destroy');
        Route::post('/{apartment}/reserve',       [ApartmentController::class, 'reserve'])->name('reserve');
        Route::post('/{apartment}/release',       [ApartmentController::class, 'release'])->name('release');
        Route::post('/{apartment}/mark-sold',     [ApartmentController::class, 'markSold'])->name('markSold');
    });

    // ─── Shartnomalar ─────────────────────────────────────
    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/',                           [ContractController::class, 'index'])->name('index');
        Route::get('/create',                     [ContractController::class, 'create'])->name('create');
        Route::post('/',                          [ContractController::class, 'store'])->name('store');
        Route::get('/{contract}',                 [ContractController::class, 'show'])->name('show');
        Route::post('/{contract}/activate',       [ContractController::class, 'activate'])->name('activate');
        Route::post('/{contract}/cancel',         [ContractController::class, 'cancel'])->name('cancel');
        Route::post('/{contract}/payments',       [ContractController::class, 'storePayment'])->name('payments.store');
        Route::get('/{contract}/print',           [ContractController::class, 'print'])->name('print');
    });

    // ─── Mijozlar ─────────────────────────────────────────
    Route::resource('clients', ClientController::class)
         ->only(['index', 'show', 'store', 'update']);

    // ─── To'lovlar ────────────────────────────────────────
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/',        [PaymentController::class, 'index'])->name('index');
        Route::get('/overdue', [PaymentController::class, 'overdue'])->name('overdue');
    });

    // ─── CRM — Potensial mijozlar ─────────────────────────
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/',                       [LeadController::class, 'index'])->name('index');
        Route::post('/',                      [LeadController::class, 'store'])->name('store');
        Route::put('/{lead}',                 [LeadController::class, 'update'])->name('update');
        Route::delete('/{lead}',              [LeadController::class, 'destroy'])->name('destroy');
        Route::post('/{lead}/status',         [LeadController::class, 'quickStatus'])->name('quickStatus');
    });

    // ─── Hisobotlar ───────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',      [ReportController::class, 'index'])->name('index');
        Route::get('/excel', [ReportController::class, 'exportExcel'])->name('excel');
    });

    // ─── AJAX / API ───────────────────────────────────────
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/blocks/{block}/status', [ApartmentController::class, 'statusApi'])->name('block.status');
        Route::get('/apartments/{apartment}', [ApartmentController::class, 'detail'])->name('apartment.detail');
    });
});
