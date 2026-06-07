<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración para crear la tabla de reseñas en la base de datos.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // Relaciona la reseña con el usuario que la escribe
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->integer('rating'); 
            $table->text('comment');   
            
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
