<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // === encuestas: evaluador_id y cerrada_en ===
        Schema::table('encuestas', function (Blueprint $table) {
            if (!Schema::hasColumn('encuestas', 'evaluador_id')) {
                $table->unsignedInteger('evaluador_id')->nullable()->after('usuario_id');
                $table->foreign('evaluador_id')
                    ->references('id')->on('usuarios')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
            if (!Schema::hasColumn('encuestas', 'cerrada_en')) {
                $table->timestamp('cerrada_en')->nullable()->after('activa');
            }
        });

        // === rendimientos: encuesta_id, anio, mes ===
        Schema::table('rendimientos', function (Blueprint $table) {
            if (!Schema::hasColumn('rendimientos', 'encuesta_id')) {
                $table->unsignedInteger('encuesta_id')->nullable()->after('ninebox_id');
                $table->foreign('encuesta_id')
                    ->references('id')->on('encuestas')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
            if (!Schema::hasColumn('rendimientos', 'anio')) {
                $table->smallInteger('anio')->nullable()->after('encuesta_id');
            }
            if (!Schema::hasColumn('rendimientos', 'mes')) {
                $table->tinyInteger('mes')->nullable()->after('anio');
            }
        });

        // === preguntas: orden ===
        Schema::table('preguntas', function (Blueprint $table) {
            if (!Schema::hasColumn('preguntas', 'orden')) {
                $table->tinyInteger('orden')->nullable()->after('categoria');
            }
        });

        // === nine_box: color_hex ===
        Schema::table('nine_box', function (Blueprint $table) {
            if (!Schema::hasColumn('nine_box', 'color_hex')) {
                $table->string('color_hex', 7)->nullable()->after('descripcion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nine_box', function (Blueprint $table) {
            if (Schema::hasColumn('nine_box', 'color_hex')) {
                $table->dropColumn('color_hex');
            }
        });

        Schema::table('preguntas', function (Blueprint $table) {
            if (Schema::hasColumn('preguntas', 'orden')) {
                $table->dropColumn('orden');
            }
        });

        Schema::table('rendimientos', function (Blueprint $table) {
            if (Schema::hasColumn('rendimientos', 'encuesta_id')) {
                $table->dropForeign(['encuesta_id']);
                $table->dropColumn('encuesta_id');
            }
            if (Schema::hasColumn('rendimientos', 'anio')) {
                $table->dropColumn('anio');
            }
            if (Schema::hasColumn('rendimientos', 'mes')) {
                $table->dropColumn('mes');
            }
        });

        Schema::table('encuestas', function (Blueprint $table) {
            if (Schema::hasColumn('encuestas', 'cerrada_en')) {
                $table->dropColumn('cerrada_en');
            }
            if (Schema::hasColumn('encuestas', 'evaluador_id')) {
                $table->dropForeign(['evaluador_id']);
                $table->dropColumn('evaluador_id');
            }
        });
    }
};
