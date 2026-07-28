<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PrintReportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// Route::get('/', function () {
//     return redirect('/admin/login');
// });

Route::redirect('/', '/admin/login');

Route::get('/print-asset-report', [InvoiceController::class, 'PrintInvoice'])->name('invoice.print');

Route::get('/print-daily-tax-collector-report', [PrintReportController::class, 'PrintDailyTaxCollectorReport'])
    ->name('daily-tax-collector-report.print')->middleware('auth'); // Ensure this route is protected by authentication
Route::get('/print-monthly-tax-report', [PrintReportController::class, 'PrintMonthlyTaxReport'])
    ->name('monthly-tax-report.print')->middleware('auth'); // Ensure this route is protected
Route::get('/print-yearly-tax-report', [PrintReportController::class, 'PrintYearlyTaxReport'])
    ->name('yearly-tax-report.print')->middleware('auth'); // Ensure this route is protected
Route::get('/print-yearly-financial-report', [PrintReportController::class, 'PrintYearlyFinancialReport'])
    ->name('yearly-financial-report.print')->middleware('auth'); // Ensure this route is protected
Route::get('/print-daily-sent-case-report', [PrintReportController::class, 'PrintDailySentCaseReport'])
    ->name('daily-sent-case-report.print')->middleware('auth'); // Ensure this route is protected
Route::get('/print-unpaid-case-report', [PrintReportController::class, 'PrintUnpaidCaseReport'])
    ->name('unpaid-case-report.print')->middleware('auth'); // Ensure this route is protected
Route::get('/print-remission-report', [PrintReportController::class, 'PrintRemissionReport'])
    ->name('remission-report.print')->middleware('auth'); // Ensure this route is protected
   


Route::get('/optimize-clear', function () {
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('event:clear');

    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    Artisan::call('optimize');

    return 'Cleared and optimized successfully.';
});
