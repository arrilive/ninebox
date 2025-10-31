<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) preguntas
        Schema::create('preguntas', function (Blueprint $table) {
            $table->increments('id');                      
            $table->text('texto');                         
            $table->string('categoria', 120);              
            $table->timestamps();                          
        });

        // 2) encuestas (pertenecen a un usuario existente)
        Schema::create('encuestas', function (Blueprint $table) {
            $table->increments('id');                      
            $table->unsignedInteger('usuario_id');         
            $table->decimal('puntaje_final', 6, 2)->nullable(); 
            $table->boolean('activa')->default(true);      
            $table->timestamps();                          

            $table->foreign('usuario_id')
                ->references('id')->on('usuarios')
                ->onUpdate('cascade')
                ->onDelete('cascade');                     
        });

        // 3) evaluaciones (detalle por pregunta dentro de una encuesta)
        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->unsignedInteger('encuesta_id');        
            $table->unsignedInteger('pregunta_id');        
            $table->integer('puntaje');                   
            $table->text('comentario')->nullable();
            $table->timestamps();

            // PK compuesta para evitar duplicados de pregunta por encuesta
            $table->primary(['encuesta_id', 'pregunta_id']);

            $table->foreign('encuesta_id')
                ->references('id')->on('encuestas')
                ->onUpdate('cascade')
                ->onDelete('cascade');                     

            $table->foreign('pregunta_id')
                ->references('id')->on('preguntas')
                ->onUpdate('cascade')
                ->onDelete('restrict');                    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // El orden inverso respeta dependencias
        Schema::dropIfExists('evaluaciones');
        Schema::dropIfExists('encuestas');
        Schema::dropIfExists('preguntas');
    }
};