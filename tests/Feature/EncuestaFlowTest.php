<?php

use App\Models\User;
use App\Models\Encuesta;
use App\Models\Evaluacion;
use App\Models\Rendimiento;
use App\Models\Pregunta;
use App\Models\NineBox;
use App\Models\ReglaNinebox;
use App\Models\Empresa;
use App\Models\Departamento;
use App\Models\TipoUsuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('jefe puede guardar borrador de encuesta', function () {
    // Arrange
    crearRoles();
    $empresa = Empresa::create(['nombre' => 'BPT', 'slug' => 'bpt', 'activa' => true]);
    $depto = Departamento::create(['nombre_departamento' => 'Ventas', 'descripcion' => 'Ventas']);
    
    $jefe = crearUsuarioConNombre('jefe1', 'jefe1@example.com', 'Jefe', getIdJefe(), $empresa->id, $depto->id);
    $depto->update(['jefe_id' => $jefe->id]);

    $empleado = crearUsuarioConNombre('empleado1', 'empleado1@example.com', 'Empleado', getIdEmpleado(), $empresa->id, $depto->id);

    // Crear preguntas
    for ($i = 1; $i <= 10; $i++) {
        Pregunta::create([
            'id' => $i,
            'texto' => "Pregunta $i",
            'categoria' => $i <= 5 ? 'desempeno' : 'potencial',
            'orden' => $i
        ]);
    }

    $payload = [
        'respuestas' => [
            ['pregunta_id' => 1, 'puntaje' => 4, 'comentario' => 'c1'],
            ['pregunta_id' => 2, 'puntaje' => 3, 'comentario' => 'c2'],
            ['pregunta_id' => 3, 'puntaje' => 5, 'comentario' => 'c3'],
            ['pregunta_id' => 4, 'puntaje' => 2, 'comentario' => 'c4'],
            ['pregunta_id' => 5, 'puntaje' => 4, 'comentario' => 'c5'],
        ],
        'comentario_general' => 'Borrador parcial'
    ];

    // Act
    $response = $this->actingAs($jefe)
        ->post("/encuestas/{$empleado->id}?anio=2025&mes=6", $payload);

    // Assert
    $response->assertRedirect();
    expect(Evaluacion::count())->toBe(5);

    $enc = Encuesta::where('usuario_id', $empleado->id)->first();
    expect($enc)->not->toBeNull()
        ->and($enc->activa)->toBeTrue()
        ->and($enc->notas_privadas)->toBe('Borrador parcial');
});

test('jefe puede completar y cerrar una encuesta', function () {
    // Arrange
    crearRoles();
    $empresa = Empresa::create(['nombre' => 'BPT', 'slug' => 'bpt', 'activa' => true]);
    $depto = Departamento::create(['nombre_departamento' => 'Ventas', 'descripcion' => 'Ventas']);
    
    $jefe = crearUsuarioConNombre('jefe1', 'jefe1@example.com', 'Jefe', getIdJefe(), $empresa->id, $depto->id);
    $depto->update(['jefe_id' => $jefe->id]);

    $empleado = crearUsuarioConNombre('empleado1', 'empleado1@example.com', 'Empleado', getIdEmpleado(), $empresa->id, $depto->id);

    crearPreguntasYNineBoxFlow();

    $payload = [
        'respuestas' => [
            ['pregunta_id' => 1, 'puntaje' => 4, 'comentario' => 'c1'],
            ['pregunta_id' => 2, 'puntaje' => 4, 'comentario' => 'c2'],
            ['pregunta_id' => 3, 'puntaje' => 4, 'comentario' => 'c3'],
            ['pregunta_id' => 4, 'puntaje' => 4, 'comentario' => 'c4'],
            ['pregunta_id' => 5, 'puntaje' => 4, 'comentario' => 'c5'],
            ['pregunta_id' => 6, 'puntaje' => 4, 'comentario' => 'c6'],
            ['pregunta_id' => 7, 'puntaje' => 4, 'comentario' => 'c7'],
            ['pregunta_id' => 8, 'puntaje' => 4, 'comentario' => 'c8'],
            ['pregunta_id' => 9, 'puntaje' => 4, 'comentario' => 'c9'],
            ['pregunta_id' => 10, 'puntaje' => 4, 'comentario' => 'c10'],
        ],
        'comentario_general' => 'Evaluacion completa'
    ];

    // Act
    $response = $this->actingAs($jefe)
        ->post("/encuestas/{$empleado->id}?anio=2025&mes=6", $payload);

    // Assert
    $response->assertRedirect(route('encuestas.empleados', ['anio' => 2025, 'mes' => 6]));

    $enc = Encuesta::where('usuario_id', $empleado->id)
        ->where('anio', 2025)
        ->where('mes', 6)
        ->first();
    expect($enc)->not->toBeNull()
        ->and($enc->activa)->toBeFalse()
        ->and($enc->total_desempeno)->toBe(20)
        ->and($enc->total_potencial)->toBe(20);

    $r = Rendimiento::where('usuario_id', $empleado->id)
        ->where('anio', 2025)
        ->where('mes', 6)
        ->first();
    expect($r)->not->toBeNull()
        ->and($r->ninebox_id)->toBe($enc->ninebox_id);
});

test('encuesta cerrada no se puede reenviar', function () {
    // Arrange
    crearRoles();
    $empresa = Empresa::create(['nombre' => 'BPT', 'slug' => 'bpt', 'activa' => true]);
    $depto = Departamento::create(['nombre_departamento' => 'Ventas', 'descripcion' => 'Ventas']);
    
    $jefe = crearUsuarioConNombre('jefe1', 'jefe1@example.com', 'Jefe', getIdJefe(), $empresa->id, $depto->id);
    $depto->update(['jefe_id' => $jefe->id]);

    $empleado = crearUsuarioConNombre('empleado1', 'empleado1@example.com', 'Empleado', getIdEmpleado(), $empresa->id, $depto->id);

    crearPreguntasYNineBoxFlow();

    $enc = new Encuesta([
        'usuario_id' => $empleado->id,
        'evaluador_id' => $jefe->id,
        'activa' => false,
        'ninebox_id' => 9
    ]);
    $enc->anio = 2025;
    $enc->mes = 6;
    $enc->save();

    Rendimiento::create([
        'usuario_id' => $empleado->id,
        'ninebox_id' => 9,
        'encuesta_id' => $enc->id,
        'anio' => 2025,
        'mes' => 6
    ]);

    $payload = [
        'respuestas' => [
            ['pregunta_id' => 1, 'puntaje' => 1, 'comentario' => 'intento']
        ]
    ];

    // Act
    $response = $this->actingAs($jefe)
        ->post("/encuestas/{$empleado->id}?anio=2025&mes=6", $payload);

    // Assert
    $response->assertRedirect(route('encuestas.show', ['empleado' => $empleado->id, 'anio' => 2025, 'mes' => 6]));
    expect(Rendimiento::where('usuario_id', $empleado->id)->where('anio', 2025)->where('mes', 6)->count())->toBe(1);
});

test('jefe no puede evaluar empleado de otro departamento', function () {
    // Arrange
    crearRoles();
    $empresa = Empresa::create(['nombre' => 'BPT', 'slug' => 'bpt', 'activa' => true]);
    
    $depto1 = Departamento::create(['nombre_departamento' => 'Depto 1', 'descripcion' => 'Depto 1']);
    $depto2 = Departamento::create(['nombre_departamento' => 'Depto 2', 'descripcion' => 'Depto 2']);

    $jefe1 = crearUsuarioConNombre('jefe1', 'jefe1@example.com', 'Jefe 1', getIdJefe(), $empresa->id, $depto1->id);
    $depto1->update(['jefe_id' => $jefe1->id]);

    $empleado2 = crearUsuarioConNombre('empleado2', 'empleado2@example.com', 'Empleado 2', getIdEmpleado(), $empresa->id, $depto2->id);

    $payload = [
        'respuestas' => [
            ['pregunta_id' => 1, 'puntaje' => 4]
        ]
    ];

    // Act
    $response = $this->actingAs($jefe1)
        ->post("/encuestas/{$empleado2->id}?anio=2025&mes=6", $payload);

    // Assert
    $response->assertStatus(403);
});

// Helpers
function crearRoles() {
    $driver = DB::getDriverName();
    if ($driver === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = OFF;');
    } else {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    }

    DB::table('tipos_usuarios')->truncate();

    $r1 = new TipoUsuario(['tipo_nombre' => 'Superadmin', 'descripcion' => 'Superadmin desc']);
    $r1->id = 1;
    $r1->save();

    $r2 = new TipoUsuario(['tipo_nombre' => 'Jefe', 'descripcion' => 'Jefe desc']);
    $r2->id = 2;
    $r2->save();

    $r3 = new TipoUsuario(['tipo_nombre' => 'Empleado', 'descripcion' => 'Empleado desc']);
    $r3->id = 3;
    $r3->save();

    $r4 = new TipoUsuario(['tipo_nombre' => 'Dueño', 'descripcion' => 'Dueño desc']);
    $r4->id = 4;
    $r4->save();

    if ($driver === 'sqlite') {
        DB::statement('PRAGMA foreign_keys = ON;');
    } else {
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

function getIdJefe() {
    return TipoUsuario::where('tipo_nombre', 'Jefe')->value('id');
}

function getIdEmpleado() {
    return TipoUsuario::where('tipo_nombre', 'Empleado')->value('id');
}

function crearUsuarioConNombre(string $userName, string $correo, string $nombre, int $tipoUsuarioId, int $empresaId, ?int $deptoId = null) {
    $u = new User([
        'user_name' => $userName,
        'password' => bcrypt('password'),
        'correo' => $correo,
        'apellido_paterno' => 'Uno',
        'tipo_usuario_id' => $tipoUsuarioId,
        'empresa_id' => $empresaId,
        'departamento_id' => $deptoId
    ]);
    $u->nombre = $nombre;
    $u->save();
    return $u;
}

function crearPreguntasYNineBoxFlow() {
    // 10 preguntas
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

    // reglas (nine_box ya está sembrado por la migración)
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
