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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            // Relación con el usuario (si borras el usuario, se borran sus tareas)
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            
            // Datos de la tarea basados en el diseño de Figma
            $table->string('title');
            $table->text('description')->nullable(); // nullable() permite que esté vacío
            $table->date('due_date')->nullable();
            $table->string('priority')->default('Media'); // Alta, Media, Baja
            $table->string('status')->default('pending'); // pending, completed
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
