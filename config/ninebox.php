<?php

return [
    'presets' => [
        // Tu orden correcto (segunda imagen)
        'SECOND_IMAGE_ORDER' => [
            [6, 8, 9], // fila superior (izq→der)
            [2, 5, 7], // fila media
            [1, 3, 4], // fila inferior
        ],
    ],
    'active_preset' => env('NINEBOX_PRESET', 'SECOND_IMAGE_ORDER'),
];