<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReturnLoanController;
use Illuminate\Support\Facades\Route;

Route::post('v1/login', [AuthController::class, 'login']); // Iniciar sesion

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logout']); // cerrar sesion
    Route::get('profile', [AuthController::class, 'profile']); // mostrar perfil

    // Books
    Route::get('books', [BookController::class, 'index']); // Listar libros

    Route::get('loans', [LoanController::class, 'index']); // Listar presatamos 

    Route::post('loans', [LoanController::class, 'store']); // Prestar libro
    Route::post('loans/{loan}/return', ReturnLoanController::class); // Devolver libros
});
