<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // === columnas (solo si no existen) ===
        Schema::table('encuestas', function (Blueprint $table) {
            if (!Schema::hasColumn('encuestas', 'jefe_id')) {
                $table->unsignedInteger('jefe_id')->after('usuario_id')->nullable();
                $table->foreign('jefe_id')->references('id')->on('usuarios')
                      ->onUpdate('cascade')->onDelete('cascade');
            }
            if (!Schema::hasColumn('encuestas', 'anio')) {
                $table->unsignedSmallInteger('anio')->after('jefe_id')->nullable();
            }
            if (!Schema::hasColumn('encuestas', 'mes')) {
                $table->unsignedTinyInteger('mes')->after('anio')->nullable();
            }
            if (!Schema::hasColumn('encuestas', 'enviada_en')) {
                $table->timestamp('enviada_en')->nullable()->after('puntaje_final');
            }
        });

        // === índice único compuesto (sin Doctrine) ===
        $driver = DB::getDriverName();

        $indexName = 'encuestas_usuario_jefe_periodo_unique';
        $hasIndex = false;

        if ($driver === 'mysql') {
            $db = DB::getDatabaseName();
            $res = DB::select("
                SELECT 1
                FROM information_schema.statistics
                WHERE table_schema = ? AND table_name = 'encuestas' AND index_name = ?
                LIMIT 1
            ", [$db, $indexName]);
            $hasIndex = !empty($res);
        } elseif ($driver === 'pgsql') {
            $res = DB::select("
                SELECT 1 FROM pg_indexes
                WHERE schemaname = ANY(current_schemas(false))
                  AND tablename = 'encuestas'
                  AND indexname = ?
                LIMIT 1
            ", [$indexName]);
            $hasIndex = !empty($res);
        } elseif ($driver === 'sqlite') {
            $res = DB::select("PRAGMA index_list('encuestas')");
            $hasIndex = collect($res)->contains(function ($row) use ($indexName) {
                // sqlite puede devolver name como propiedad u índice 1
                $name = is_object($row) ? ($row->name ?? null) : ($row['name'] ?? null);
                return $name === $indexName;
            });
        }

        if (!$hasIndex) {
            Schema::table('encuestas', function (Blueprint $table) use ($indexName) {
                $table->unique(['usuario_id','jefe_id','anio','mes'], $indexName);
            });
        }
    }

    public function down(): void
    {
        // Quitar índice si existe (sin Doctrine)
        $indexName = 'encuestas_usuario_jefe_periodo_unique';

        try {
            Schema::table('encuestas', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        } catch (\Throwable $e) {
            // noop: el índice puede no existir, evitamos reventar el rollback
        }

        Schema::table('encuestas', function (Blueprint $table) {
            if (Schema::hasColumn('encuestas', 'jefe_id')) {
                $table->dropForeign(['jefe_id']);
                $table->dropColumn('jefe_id');
            }
            if (Schema::hasColumn('encuestas', 'anio')) {
                $table->dropColumn('anio');
            }
            if (Schema::hasColumn('encuestas', 'mes')) {
                $table->dropColumn('mes');
            }
            if (Schema::hasColumn('encuestas', 'enviada_en')) {
                $table->dropColumn('enviada_en');
            }
        });
    }
};
