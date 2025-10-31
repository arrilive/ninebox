<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // === 1) Ampliar tabla encuestas ===
        Schema::table('encuestas', function (Blueprint $table) {
            // Totales por categoría
            $table->unsignedTinyInteger('total_desempeno')->nullable()->after('usuario_id');
            $table->unsignedTinyInteger('total_potencial')->nullable()->after('total_desempeno');

            // Cuadrante asignado (FK a nine_box)
            $table->unsignedInteger('ninebox_id')->nullable()->after('puntaje_final');
            $table->foreign('ninebox_id')
                ->references('id')->on('nine_box')
                ->onUpdate('cascade')
                ->onDelete('set null');

            // Campos de feedback
            $table->text('feedback_publico')->nullable()->after('activa');
            $table->text('notas_privadas')->nullable()->after('feedback_publico');
        });

        // === 2) Crear reglas_ninebox ===
        Schema::create('reglas_ninebox', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('min_desempeno');
            $table->unsignedTinyInteger('max_desempeno');
            $table->unsignedTinyInteger('min_potencial');
            $table->unsignedTinyInteger('max_potencial');
            $table->unsignedInteger('ninebox_id');
            $table->string('etiqueta', 120)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('ninebox_id')
                ->references('id')->on('nine_box')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
        
    }

    public function down(): void
    {
        Schema::dropIfExists('reglas_ninebox');

        Schema::table('encuestas', function (Blueprint $table) {
            if (Schema::hasColumn('encuestas', 'ninebox_id')) {
                $table->dropForeign(['ninebox_id']);
                $table->dropColumn(['ninebox_id']);
            }
            if (Schema::hasColumn('encuestas', 'total_potencial')) {
                $table->dropColumn(['total_potencial', 'total_desempeno']);
            }
            if (Schema::hasColumn('encuestas', 'feedback_publico')) {
                $table->dropColumn(['feedback_publico', 'notas_privadas']);
            }
        });
    }
};
