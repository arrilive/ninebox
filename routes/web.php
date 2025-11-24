<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EncuestaController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('ninebox.dashboard');
    }
    return redirect()->route('login');
});

// === Rutas autenticadas ===
Route::middleware(['auth'])->group(function () {

    // === Nine-Box (Vista principal) ===
    Route::get('/ninebox/dashboard', [DashboardController::class, 'index'])
        ->name('ninebox.dashboard');

    // === Encuestas (CRUD completo movido desde Nine-Box) ===
    Route::middleware(['puede.evaluar'])->group(function () {
        
        // Listado de empleados)
        Route::get('/encuestas/empleados', [EncuestaController::class, 'listaEmpleados'])
            ->name('encuestas.empleados');

        // Formulario de encuesta individual (edición o vista)
        Route::get('/encuestas/{empleado}', [EncuestaController::class, 'show'])
            ->whereNumber('empleado')
            ->name('encuestas.show');

        // Envío/actualización (idempotente; guarda si hay 10/10 respuestas)
        Route::post('/encuestas/{empleado}', [EncuestaController::class, 'submit'])
            ->whereNumber('empleado')
            ->name('encuestas.submit');
    });

    // === Perfil del usuario ===
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';