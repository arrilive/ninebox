<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            // Si 'texto' es TEXT, ver nota abajo.
            $table->unique(['texto', 'categoria'], 'preguntas_texto_categoria_unique');
        });
    }

    public function down(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->dropUnique('preguntas_texto_categoria_unique');
        });
    }
};
