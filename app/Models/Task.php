<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // 1. Permitir que estos campos se guarden en la base de datos
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'priority',
        'category',
        'status'
    ];

    // 2. Relación inversa: Una tarea pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class);
    }
}
