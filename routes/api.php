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
    Route::get('books/{book}', [BookController::class, 'show']); // Mostrar detalle de libro
    Route::post('books', [BookController::class, 'store']); // Crear libro
    Route::put('books/{book}', [BookController::class, 'update']); // Actualizacion completa de libro
    Route::patch('books/{book}', [BookController::class, 'partialUpdate']); // Edicion parcial de libro
    Route::delete('books/{book}', [BookController::class, 'destroy']); // Eliminar libro

    Route::get('loans', [LoanController::class, 'index']); // Listar presatamos 

    Route::post('loans', [LoanController::class, 'store']); // Prestar libro
    Route::post('loans/{loan}/return', ReturnLoanController::class); // Devolver libros
});
