<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoansController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\RepaymentScheduleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function() {
    //for user logout
    Route::get('/logout', LogoutController::class)->name('user.logout');

    // loan api
    Route::get('/loan', [LoansController::class, 'index'])->name('loan.list');
    Route::get('/loan/{id}', [LoansController::class, 'show'])->name('loan.show');
    Route::post('/loan', [LoansController::class, 'store'])->name('loan.apply');
    Route::post('/loan-repayment', RepaymentScheduleController::class)->name('loan.repayment');

    // approve loan
    Route::patch('/approve-loan', [LoansController::class, 'approve'])->name('loan.approve')->middleware('isAdmin');
});

Route::post('/register', RegisterController::class)->name('user.register');
Route::post('/login', LoginController::class)->name('user.login');