<?php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('ninebox.dashboard');
    }
    return view('welcome'); 
});

Route::middleware(['auth'])->group(function () {
    // Dashboard del jefe
    Route::get('/ninebox/dashboard', [DashboardController::class, 'index'])->name('ninebox.dashboard');
    Route::post('/ninebox/guardar-evaluacion', [DashboardController::class, 'guardarEvaluacion'])
        ->name('ninebox.guardar.evaluacion');
    Route::post('/ninebox/filtrar-rendimientos', [DashboardController::class, 'filtrarRendimientosPorFecha'])
        ->name('ninebox.filtrar.rendimientos');
    
    // Perfil del usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';