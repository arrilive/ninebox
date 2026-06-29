<?php

use App\Services\EvaluacionService;
use App\Models\Encuesta;
use App\Models\Evaluacion;
use App\Models\Rendimiento;
use App\Models\ReglaNinebox;
use App\Models\NineBox;
use App\Models\Pregunta;
use App\Models\User;
use App\Models\Empresa;
use App\Models\TipoUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('guardarRespuestas guarda las respuestas de la encuesta', function () {
    // Arrange
    $empresa = crearEmpresa();
    $tipoUsuario = crearTipoUsuario('Empleado');
    $evaluado = crearUsuario($tipoUsuario->id, $empresa->id);
    $evaluador = crearUsuario($tipoUsuario->id, $empresa->id);
    $encuesta = crearEncuesta($evaluado->id, $evaluador->id);
    crearPreguntasYNineBox();

    $respuestas = [];
    for ($i = 1; $i <= 10; $i++) {
        $respuestas[] = [
            'pregunta_id' => $i,
            'puntaje' => 4,
            'comentario' => "comentario $i"
        ];
    }

    $service = new EvaluacionService();

    // Act
    $service->guardarRespuestas($encuesta, $respuestas);

    // Assert
    expect(Evaluacion::where('encuesta_id', $encuesta->id)->count())->toBe(10);
});

test('guardarRespuestas elimina la respuesta cuando el puntaje es null', function () {
    // Arrange
    $empresa = crearEmpresa();
    $tipoUsuario = crearTipoUsuario('Empleado');
    $evaluado = crearUsuario($tipoUsuario->id, $empresa->id);
    $evaluador = crearUsuario($tipoUsuario->id, $empresa->id);
    $encuesta = crearEncuesta($evaluado->id, $evaluador->id);
    crearPreguntasYNineBox();

    Evaluacion::create([
        'encuesta_id' => $encuesta->id,
        'pregunta_id' => 1,
        'puntaje' => 3,
        'comentario' => 'inicial'
    ]);

    $service = new EvaluacionService();

    // Act
    $service->guardarRespuestas($encuesta, [
        ['pregunta_id' => 1, 'puntaje' => null]
    ]);

    // Assert
    expect(Evaluacion::where('encuesta_id', $encuesta->id)->where('pregunta_id', 1)->doesntExist())->toBeTrue();
});

test('calcularTotales suma correctamente por categoria', function () {
    // Arrange
    $empresa = crearEmpresa();
    $tipoUsuario = crearTipoUsuario('Empleado');
    $evaluado = crearUsuario($tipoUsuario->id, $empresa->id);
    $evaluador = crearUsuario($tipoUsuario->id, $empresa->id);
    $encuesta = crearEncuesta($evaluado->id, $evaluador->id);
    crearPreguntasYNineBox();

    // 5 de desempeno (puntaje=4 cada una)
    for ($i = 1; $i <= 5; $i++) {
        Evaluacion::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $i,
            'puntaje' => 4,
            'comentario' => 'd'
        ]);
    }
    // 5 de potencial (puntaje=2 cada una)
    for ($i = 6; $i <= 10; $i++) {
        Evaluacion::create([
            'encuesta_id' => $encuesta->id,
            'pregunta_id' => $i,
            'puntaje' => 2,
            'comentario' => 'p'
        ]);
    }

    $service = new EvaluacionService();

    // Act
    $totales = $service->calcularTotales($encuesta);

    // Assert
    expect($totales['desempeno'])->toBe(20)
        ->and($totales['potencial'])->toBe(10);
});

test('resolverCuadrante lanza RuntimeException si no existe regla', function () {
    // Arrange
    $service = new EvaluacionService();

    // Act & Assert
    expect(fn() => $service->resolverCuadrante(0, 0))->toThrow(\RuntimeException::class);
});

test('resolverCuadrante retorna la ReglaNinebox correcta', function () {
    // Arrange
    crearPreguntasYNineBox();
    $service = new EvaluacionService();

    // Act
    $regla = $service->resolverCuadrante(25, 25);

    // Assert
    expect($regla)->toBeInstanceOf(ReglaNinebox::class)
        ->and($regla->ninebox_id)->toBe(9);
});

test('cerrarEncuesta graba todos los campos correctamente', function () {
    // Arrange
    $empresa = crearEmpresa();
    $tipoUsuario = crearTipoUsuario('Empleado');
    $evaluado = crearUsuario($tipoUsuario->id, $empresa->id);
    $evaluador = crearUsuario($tipoUsuario->id, $empresa->id);
    $encuesta = crearEncuesta($evaluado->id, $evaluador->id);
    crearPreguntasYNineBox();
    $regla = ReglaNinebox::where('ninebox_id', 9)->first();

    $service = new EvaluacionService();

    // Act
    $service->cerrarEncuesta($encuesta, 20, 20, $regla, 2025, 6, $evaluador);

    // Assert
    expect($encuesta->activa)->toBeFalse()
        ->and($encuesta->total_desempeno)->toBe(20)
        ->and($encuesta->total_potencial)->toBe(20)
        ->and($encuesta->anio)->toBe(2025)
        ->and($encuesta->mes)->toBe(6)
        ->and($encuesta->evaluador_id)->toBe($evaluador->id)
        ->and($encuesta->cerrada_en)->not->toBeNull();
});

test('registrarRendimiento crea un rendimiento con los datos correctos', function () {
    // Arrange
    $empresa = crearEmpresa();
    $tipoUsuario = crearTipoUsuario('Empleado');
    $evaluado = crearUsuario($tipoUsuario->id, $empresa->id);
    $evaluador = crearUsuario($tipoUsuario->id, $empresa->id);
    $encuesta = crearEncuesta($evaluado->id, $evaluador->id);
    crearPreguntasYNineBox();
    $regla = ReglaNinebox::where('ninebox_id', 9)->first();

    $service = new EvaluacionService();
    $service->cerrarEncuesta($encuesta, 20, 20, $regla, 2025, 6, $evaluador);

    // Act
    $r = $service->registrarRendimiento($encuesta);

    // Assert
    expect($r->usuario_id)->toBe($encuesta->usuario_id)
        ->and($r->ninebox_id)->toBe($encuesta->ninebox_id)
        ->and($r->encuesta_id)->toBe($encuesta->id)
        ->and($r->anio)->toBe($encuesta->anio)
        ->and($r->mes)->toBe($encuesta->mes);
});

test('registrarRendimiento reemplaza el rendimiento existente del mismo periodo', function () {
    // Arrange
    $empresa = crearEmpresa();
    $tipoUsuario = crearTipoUsuario('Empleado');
    $evaluado = crearUsuario($tipoUsuario->id, $empresa->id);
    $evaluador = crearUsuario($tipoUsuario->id, $empresa->id);
    $encuesta = crearEncuesta($evaluado->id, $evaluador->id);
    crearPreguntasYNineBox();
    
    // Crear rendimiento previo para el mismo periodo
    $previoEncuesta = crearEncuesta($evaluado->id, $evaluador->id);
    Rendimiento::create([
        'usuario_id' => $evaluado->id,
        'ninebox_id' => 1, // Cuadrante distinto
        'encuesta_id' => $previoEncuesta->id,
        'anio' => 2025,
        'mes' => 6,
        'comentario' => 'previo'
    ]);

    $regla = ReglaNinebox::where('ninebox_id', 9)->first();
    $service = new EvaluacionService();
    $service->cerrarEncuesta($encuesta, 20, 20, $regla, 2025, 6, $evaluador);

    // Act
    $service->registrarRendimiento($encuesta);

    // Assert
    expect(Rendimiento::where('usuario_id', $evaluado->id)->where('anio', 2025)->where('mes', 6)->count())->toBe(1);
});

// Helpers
function crearEmpresa() {
    return Empresa::create([
        'nombre' => 'Mi Empresa',
        'slug' => 'mi-empresa',
        'activa' => true
    ]);
}

function crearTipoUsuario(string $nombre) {
    return TipoUsuario::create([
        'tipo_nombre' => $nombre,
        'descripcion' => $nombre . ' desc'
    ]);
}

function crearUsuario(int $tipoId, int $empresaId) {
    $u = new User([
        'user_name' => 'user_' . uniqid(),
        'password' => bcrypt('password'),
        'correo' => 'user_' . uniqid() . '@example.com',
        'apellido_paterno' => 'Paterno',
        'apellido_materno' => 'Materno',
        'tipo_usuario_id' => $tipoId,
        'empresa_id' => $empresaId
    ]);
    $u->nombre = 'Nombre';
    $u->save();
    return $u;
}

function crearEncuesta(int $evaluadoId, int $evaluadorId) {
    return Encuesta::create([
        'usuario_id' => $evaluadoId,
        'evaluador_id' => $evaluadorId,
        'activa' => true,
        'anio' => 2025,
        'mes' => 6
    ]);
}

function crearPreguntasYNineBox() {
    // 10 preguntas (5 desempeno, 5 potencial)
    for ($i = 1; $i <= 5; $i++) {
        Pregunta::create([
            'id' => $i,
            'texto' => "Pregunta Desempeño $i",
            'categoria' => 'desempeno',
            'orden' => $i
        ]);
    }
    for ($i = 6; $i <= 10; $i++) {
        Pregunta::create([
            'id' => $i,
            'texto' => "Pregunta Potencial $i",
            'categoria' => 'potencial',
            'orden' => $i
        ]);
    }

    // reglas (nine_box ya está sembrado por la migración, así que solo creamos las reglas)
    $D = [
        'bajo'  => [5,  12],
        'medio' => [13, 19],
        'alto'  => [20, 25],
    ];
    $P = [
        'bajo'  => [5,  12],
        'medio' => [13, 19],
        'alto'  => [20, 25],
    ];
    $reglas = [
        [6, 'Bajo desempeño / Alto potencial',   $D['bajo'][0],  $D['bajo'][1],  $P['alto'][0], $P['alto'][1]],
        [8, 'Medio desempeño / Alto potencial',  $D['medio'][0], $D['medio'][1], $P['alto'][0], $P['alto'][1]],
        [9, 'Alto desempeño / Alto potencial',   $D['alto'][0],  $D['alto'][1],  $P['alto'][0], $P['alto'][1]],
        [2, 'Bajo desempeño / Medio potencial',  $D['bajo'][0],  $D['bajo'][1],  $P['medio'][0], $P['medio'][1]],
        [5, 'Medio desempeño / Medio potencial', $D['medio'][0], $D['medio'][1], $P['medio'][0], $P['medio'][1]],
        [7, 'Alto desempeño / Medio potencial',  $D['alto'][0],  $D['alto'][1],  $P['medio'][0], $P['medio'][1]],
        [1, 'Bajo desempeño / Bajo potencial',   $D['bajo'][0],  $D['bajo'][1],  $P['bajo'][0], $P['bajo'][1]],
        [3, 'Medio desempeño / Bajo potencial',  $D['medio'][0], $D['medio'][1], $P['bajo'][0], $P['bajo'][1]],
        [4, 'Alto desempeño / Bajo potencial',   $D['alto'][0],  $D['alto'][1],  $P['bajo'][0], $P['bajo'][1]],
    ];
    foreach ($reglas as [$nineboxId, $etiqueta, $minD, $maxD, $minP, $maxP]) {
        ReglaNinebox::create([
            'ninebox_id'    => $nineboxId,
            'etiqueta'      => $etiqueta,
            'min_desempeno' => $minD,
            'max_desempeno' => $maxD,
            'min_potencial' => $minP,
            'max_potencial' => $maxP,
            'activo'        => true,
        ]);
    }
}
