<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Muestra la lista de tareas en el Dashboard.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // ¡LA MAGIA AQUÍ!: Añadimos with('subtasks') para enviar las subtareas a Vue
        $tasks = $user->tasks()->with('subtasks')->latest()->get();

        return Inertia::render('Dashboard', [
            'tasks' => $tasks
        ]);
    }

    /**
     * Guarda una nueva tarea en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:Alta,Media,Baja',
            'category' => 'required|string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->tasks()->create($validated);

        return redirect()->route('dashboard');
    }

    /**
     * Cambia el estado de una tarea (de pendiente a completada o viceversa).
     */
    public function toggleStatus(Task $task)
    {
        // Verificamos que la tarea pertenezca al usuario logueado por seguridad
        if ($task->user_id === Auth::id()) {
            $task->update([
                'status' => $task->status === 'pending' ? 'completed' : 'pending'
            ]);
        }
        return redirect()->back(); // Recarga los datos silenciosamente
    }

    /**
     * Elimina una tarea de la base de datos.
     */
    public function destroy(Task $task)
    {
        if ($task->user_id === Auth::id()) {
            $task->delete();
        }
        return redirect()->back();
    }

    /**
     * Actualiza una tarea existente en la base de datos.
     */
    public function update(Request $request, Task $task)
    {
        // Verifica que la tarea pertenezca al usuario logueado
        if ($task->user_id === Auth::id()) {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'due_date' => 'nullable|date',
                'priority' => 'required|in:Alta,Media,Baja',
                'category' => 'required|string',
            ]);

            $task->update($validated);
        }

        return redirect()->back();
    }
}
