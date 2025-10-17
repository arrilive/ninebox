<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JefeDashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [JefeDashboardController::class, 'index'])->name('jefe.dashboard');
    
    // Rutas para 9-box
    Route::get('/jefe/cuadrante/{nineboxId}/empleados', [JefeDashboardController::class, 'obtenerEmpleadosCuadrante'])
        ->name('jefe.cuadrante.empleados');
    Route::post('/jefe/asignar-empleado', [JefeDashboardController::class, 'asignarEmpleado'])
        ->name('jefe.asignar.empleado');
    Route::post('/jefe/eliminar-asignacion', [JefeDashboardController::class, 'eliminarAsignacion'])
        ->name('jefe.eliminar.asignacion');
    Route::post('/jefe/guardar-evaluacion', [JefeDashboardController::class, 'guardarEvaluacion'])
        ->name('jefe.guardar.evaluacion');
    
    // Rutas del perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';