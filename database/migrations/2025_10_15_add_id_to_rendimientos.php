<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rendimientos', function (Blueprint $table) {
            $table->unique(['usuario_id', 'ninebox_id', 'created_at'], 'uk_usuario_ninebox_createdat');
        });
    }

    public function down(): void
    {
        Schema::table('rendimientos', function (Blueprint $table) {
            $table->dropUnique('uk_usuario_ninebox_createdat');
        });
    }
};
