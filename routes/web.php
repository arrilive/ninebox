<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EncuestaController;
use App\Http\Controllers\Admin;

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

    // === Administración (Superadmin) ===
    Route::middleware(['solo.superadmin'])->prefix('admin')->name('admin.')->group(function () {

        // Empresas
        Route::get('/empresas', [Admin\EmpresaController::class, 'index'])
            ->name('empresas.index');
        Route::get('/empresas/crear', [Admin\EmpresaController::class, 'crear'])
            ->name('empresas.crear');
        Route::post('/empresas', [Admin\EmpresaController::class, 'store'])
            ->name('empresas.store');
        Route::get('/empresas/{empresa}', [Admin\EmpresaController::class, 'show'])
            ->name('empresas.show');
        Route::patch('/empresas/{empresa}/toggle', [Admin\EmpresaController::class, 'toggleActiva'])
            ->name('empresas.toggle');

        // Departamentos (dentro de una empresa)
        Route::get('/empresas/{empresa}/departamentos/crear', [Admin\DepartamentoController::class, 'crear'])
            ->name('departamentos.crear');
        Route::post('/empresas/{empresa}/departamentos', [Admin\DepartamentoController::class, 'store'])
            ->name('departamentos.store');
        Route::delete('/empresas/{empresa}/departamentos/{departamento}', [Admin\DepartamentoController::class, 'destroy'])
            ->name('departamentos.destroy');

        // Usuarios
        Route::get('/empresas/{empresa}/usuarios/crear', [Admin\UsuarioController::class, 'crear'])
            ->name('usuarios.crear');
        Route::post('/empresas/{empresa}/usuarios', [Admin\UsuarioController::class, 'store'])
            ->name('usuarios.store');
        Route::delete('/empresas/{empresa}/usuarios/{usuario}', [Admin\UsuarioController::class, 'destroy'])
            ->name('usuarios.destroy');
    });

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