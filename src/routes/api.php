<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoansController;
use App\Http\Controllers\RepaymentScheduleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('v1')->group(function(){
    // Public routes
    Route::post('/login', [AuthController::class, 'login'])->name('customer.login');
    Route::post('/register', [AuthController::class, 'register'])->name('customer.register');

    // protected routes
    Route::group(['middleware' => ['auth:sanctum']], function(){
        Route::post('/logout', [AuthController::class, 'logout'])->name('customer.logout');

        #loan
        Route::get('/loan', [LoansController::class, 'index'])->name('loan.list');
        Route::get('/loan/{id}', [LoansController::class, 'show'])->name('loan.show');
        Route::post('/loan', [LoansController::class, 'store'])->name('loan.apply');
        Route::post('/loan/repayment', RepaymentScheduleController::class)->name('loan.repayment');

        # ForAdmin
        Route::post('/loan/approve', [LoansController::class, 'approve'])->name('loan.approve')->middleware('isAdmin');

    });
});
