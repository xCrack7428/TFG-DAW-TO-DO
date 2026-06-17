<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Subtask;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate(['title' => 'required|string|max:255']);

        $task->subtasks()->create([
            'title' => $request->title,
            'is_completed' => false
        ]);

        // Si añadimos una subtarea, la tarea principal vuelve a estar pendiente
        $task->update(['status' => 'pending']);

        return back();
    }

    public function toggle(Subtask $subtask)
    {
        // Cambiamos el estado de la subtarea (de false a true, o viceversa)
        $subtask->update(['is_completed' => !$subtask->is_completed]);

        $task = $subtask->task;

        // Contamos cuántas subtareas quedan sin hacer
        $pendingSubtasks = $task->subtasks()->where('is_completed', false)->count();

        // Si quedan 0, la tarea principal se completa. Si queda alguna, vuelve a pendiente.
        if ($pendingSubtasks === 0) {
            $task->update(['status' => 'completed']);
        } else {
            $task->update(['status' => 'pending']);
        }

        return back();
    }
}
