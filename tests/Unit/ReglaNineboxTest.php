<?php

use App\Models\NineBox;
use App\Models\ReglaNinebox;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('resuelve cuadrante estrella con desempeno alto y potencial alto', function () {
    // Arrange: obtener nine_box posicion=9
    $ninebox = NineBox::where('posicion', 9)->first();

    ReglaNinebox::create([
        'min_desempeno' => 20,
        'max_desempeno' => 25,
        'min_potencial' => 20,
        'max_potencial' => 25,
        'ninebox_id' => $ninebox->id,
        'etiqueta' => 'Alto desempeño / Alto potencial',
        'activo' => true
    ]);

    // Act
    $result = ReglaNinebox::resolver(20, 20);

    // Assert
    expect($result)->not->toBeNull()
        ->and($result->ninebox_id)->toBe($ninebox->id);
});

test('resuelve cuadrante inaceptable con desempeno bajo y potencial bajo', function () {
    // Arrange: obtener nine_box posicion=1
    $ninebox = NineBox::where('posicion', 1)->first();

    ReglaNinebox::create([
        'min_desempeno' => 5,
        'max_desempeno' => 12,
        'min_potencial' => 5,
        'max_potencial' => 12,
        'ninebox_id' => $ninebox->id,
        'etiqueta' => 'Bajo desempeño / Bajo potencial',
        'activo' => true
    ]);

    // Act
    $result = ReglaNinebox::resolver(5, 5);

    // Assert
    expect($result)->not->toBeNull()
        ->and($result->ninebox_id)->toBe($ninebox->id);
});

test('resuelve cuadrante personal solido con desempeno medio y potencial medio', function () {
    // Arrange: obtener nine_box posicion=5
    $ninebox = NineBox::where('posicion', 5)->first();

    ReglaNinebox::create([
        'min_desempeno' => 13,
        'max_desempeno' => 19,
        'min_potencial' => 13,
        'max_potencial' => 19,
        'ninebox_id' => $ninebox->id,
        'etiqueta' => 'Medio desempeño / Medio potencial',
        'activo' => true
    ]);

    // Act
    $result1 = ReglaNinebox::resolver(13, 13);
    $result2 = ReglaNinebox::resolver(19, 19);

    // Assert
    expect($result1)->not->toBeNull()
        ->and($result2)->not->toBeNull()
        ->and($result1->ninebox_id)->toBe($ninebox->id)
        ->and($result2->ninebox_id)->toBe($ninebox->id);
});

test('retorna null cuando no existe regla para los valores dados', function () {
    // Arrange: tabla reglas_ninebox vacía (RefreshDatabase ya la limpia)
    
    // Act
    $result = ReglaNinebox::resolver(0, 0);

    // Assert
    expect($result)->toBeNull();
});

test('los umbrales no se solapan entre niveles', function () {
    // Arrange: las 9 reglas completas con los umbrales de ARCHITECTURE.md
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
        // Potencial ALTO
        [6, 'Bajo desempeño / Alto potencial',   $D['bajo'][0],  $D['bajo'][1],  $P['alto'][0], $P['alto'][1]],
        [8, 'Medio desempeño / Alto potencial',  $D['medio'][0], $D['medio'][1], $P['alto'][0], $P['alto'][1]],
        [9, 'Alto desempeño / Alto potencial',   $D['alto'][0],  $D['alto'][1],  $P['alto'][0], $P['alto'][1]],
        // Potencial MEDIO
        [2, 'Bajo desempeño / Medio potencial',  $D['bajo'][0],  $D['bajo'][1],  $P['medio'][0], $P['medio'][1]],
        [5, 'Medio desempeño / Medio potencial', $D['medio'][0], $D['medio'][1], $P['medio'][0], $P['medio'][1]],
        [7, 'Alto desempeño / Medio potencial',  $D['alto'][0],  $D['alto'][1],  $P['medio'][0], $P['medio'][1]],
        // Potencial BAJO
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

    // Act & Assert
    // resolver(12, 12) debe retornar ninebox con bajo/bajo (ninebox_id = 1)
    $res12_12 = ReglaNinebox::resolver(12, 12);
    expect($res12_12)->not->toBeNull()
        ->and($res12_12->ninebox_id)->toBe(1);

    // resolver(13, 13) debe retornar ninebox con medio/medio (ninebox_id = 5)
    $res13_13 = ReglaNinebox::resolver(13, 13);
    expect($res13_13)->not->toBeNull()
        ->and($res13_13->ninebox_id)->toBe(5);

    // resolver(20, 20) debe retornar ninebox con alto/alto (ninebox_id = 9)
    $res20_20 = ReglaNinebox::resolver(20, 20);
    expect($res20_20)->not->toBeNull()
        ->and($res20_20->ninebox_id)->toBe(9);

    // resolver(12, 13) debe retornar ninebox con bajo desempeño / medio potencial (ninebox_id = 2)
    $res12_13 = ReglaNinebox::resolver(12, 13);
    expect($res12_13)->not->toBeNull()
        ->and($res12_13->ninebox_id)->toBe(2);
});
