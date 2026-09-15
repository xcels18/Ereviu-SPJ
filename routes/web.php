<?php

use App\Http\Controllers\AuditTrail\AuditTrailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Opd\OpdController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\ReviewGuide\ReviewGuideController;
use App\Http\Controllers\Sp2d\Sp2dController;
use App\Http\Controllers\Spj\SpjController;
use App\Http\Controllers\SpjCompile\SpjCompileController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // OPD Routes
    Route::middleware(['permission:manage_opd'])->group(function () {
        Route::get('/opd', [OpdController::class, 'index'])->name('opd.index');
        Route::get('/opd/create', [OpdController::class, 'create'])->name('opd.create');
        Route::post('/opd', [OpdController::class, 'store'])->name('opd.store');
        Route::get('/opd/{opd}', [OpdController::class, 'show'])->name('opd.show');
        Route::get('/opd/{opd}/edit', [OpdController::class, 'edit'])->name('opd.edit');
        Route::put('/opd/{opd}', [OpdController::class, 'update'])->name('opd.update');
        Route::delete('/opd/{opd}', [OpdController::class, 'destroy'])->name('opd.destroy');
    });

    // SP2D Routes
    Route::middleware(['permission:manage_sp2d'])->group(function () {
        Route::get('/sp2d', [Sp2dController::class, 'index'])->name('sp2d.index');
        Route::get('/sp2d/create', [Sp2dController::class, 'create'])->name('sp2d.create');
        Route::get('/sp2d/template', [Sp2dController::class, 'downloadTemplate'])->name('sp2d.template');
        Route::post('/sp2d', [Sp2dController::class, 'store'])->name('sp2d.store');
        Route::get('/sp2d/{sp2d}', [Sp2dController::class, 'show'])->name('sp2d.show');
        Route::get('/sp2d/{sp2d}/edit', [Sp2dController::class, 'edit'])->name('sp2d.edit');
        Route::put('/sp2d/{sp2d}', [Sp2dController::class, 'update'])->name('sp2d.update');
        Route::delete('/sp2d/{sp2d}', [Sp2dController::class, 'destroy'])->name('sp2d.destroy');
        Route::post('/sp2d/import', [Sp2dController::class, 'import'])->name('sp2d.import');
        Route::get('/sp2d/{sp2d}/budget-codes', [Sp2dController::class, 'budgetCodes'])->name('sp2d.budget-codes');
    });

    // SPJ Upload & Reviu Routes
    Route::middleware(['permission:upload_spj|review_spj'])->group(function () {
        Route::get('/spj/upload', [SpjController::class, 'upload'])->name('spj.upload');
        Route::post('/spj', [SpjController::class, 'store'])->name('spj.store');
        Route::get('/spj/documents/{document}/download', [SpjController::class, 'downloadDocument'])->name('spj.documents.download');
        Route::post('/spj/{spj}/review', [SpjController::class, 'reviewStore'])->name('spj.review.store');
        Route::post('/spj/{spj}/documents', [SpjController::class, 'addDocuments'])->name('spj.documents.store');
        Route::delete('/spj/documents/{document}', [SpjController::class, 'destroyDocument'])->name('spj.documents.destroy');
    });

    // Review Guide Routes
    Route::middleware(['permission:manage_review_guide'])->group(function () {
        Route::get('/review-guides', [ReviewGuideController::class, 'index'])->name('review-guides.index');
        Route::get('/review-guides/create', [ReviewGuideController::class, 'create'])->name('review-guides.create');
        Route::post('/review-guides', [ReviewGuideController::class, 'store'])->name('review-guides.store');
        Route::get('/review-guides/{reviewGuide}/edit', [ReviewGuideController::class, 'edit'])->name('review-guides.edit');
        Route::put('/review-guides/{reviewGuide}', [ReviewGuideController::class, 'update'])->name('review-guides.update');
        Route::delete('/review-guides/{reviewGuide}', [ReviewGuideController::class, 'destroy'])->name('review-guides.destroy');
        Route::post('/review-guides/import', [ReviewGuideController::class, 'import'])->name('review-guides.import');
        Route::get('/review-guides/export', [ReviewGuideController::class, 'export'])->name('review-guides.export');
    });

    // User Management Routes
    Route::middleware(['permission:manage_users'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Report Routes
    Route::middleware(['permission:view_reports'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/spj', [ReportController::class, 'spjReport'])->name('reports.spj');
        Route::get('/reports/findings', [ReportController::class, 'findingsReport'])->name('reports.findings');
        Route::get('/reports/opd/{opd}/sp2d', [ReportController::class, 'sp2dList'])->name('reports.sp2d');
        Route::get('/reports/sp2d/{sp2d}', [ReportController::class, 'sp2dDetail'])->name('reports.sp2d.detail');
    });
    Route::middleware(['permission:export_reports'])->group(function () {
        Route::get('/reports/cetak', [ReportController::class, 'cetakLaporan'])->name('reports.cetak');
    });

    // Compile SPJ Routes (admin)
    Route::middleware(['permission:compile_spj'])->group(function () {
        Route::get('/spj-compile', [SpjCompileController::class, 'index'])->name('spj-compile.index');
        Route::get('/spj-compile/opd/{opd}/sp2d', [SpjCompileController::class, 'getSp2dList'])->name('spj-compile.sp2d-list');
        Route::post('/spj-compile/budget-codes', [SpjCompileController::class, 'getBudgetCodes'])->name('spj-compile.budget-codes');
        Route::get('/spj-compile/history', [SpjCompileController::class, 'history'])->name('spj-compile.history');
        Route::post('/spj-compile/exports/{export}/cancel', [SpjCompileController::class, 'cancel'])->name('spj-compile.cancel');
        Route::get('/spj-compile/exports/{export}/parts/{part}', [SpjCompileController::class, 'download'])->whereNumber('part')->name('spj-compile.download');
        Route::post('/spj-compile/generate', [SpjCompileController::class, 'generate'])->name('spj-compile.generate');
    });

    // Audit Trail Routes (admin)
    Route::middleware(['permission:view_audit_trail'])->group(function () {
        Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail.index');
    });
});

require __DIR__.'/auth.php';
