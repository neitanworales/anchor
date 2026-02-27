<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

  Route::middleware('company')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::post('/expenses/{expense}/files', [ExpenseController::class, 'uploadFile']);
    Route::post('/expenses/{expense}/parse-cfdi', [ExpenseController::class, 'parseCfdiXml']);

    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::post('/reports/{report}/add-expense', [ReportController::class, 'addExpense']);
    Route::post('/reports/{report}/submit', [ReportController::class, 'submit']);

    Route::post('/reports/{report}/approve', [ReportController::class, 'approve'])->middleware('role:APPROVER,ADMIN');
    Route::post('/reports/{report}/reject', [ReportController::class, 'reject'])->middleware('role:APPROVER,ADMIN');
    Route::post('/reports/{report}/mark-paid', [ReportController::class, 'markPaid'])->middleware('role:ADMIN');
    Route::get('/reports/{report}/export.csv', [ReportController::class, 'exportCsv'])->middleware('role:ADMIN');
  });
});