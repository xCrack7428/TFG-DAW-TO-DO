<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Calculamos la fecha de hace exactamente 30 días
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Obtenemos las tareas completadas el último mes y cargamos SOLO sus subtareas completadas
        $completedTasks = $user->tasks()
            ->with(['subtasks' => function ($query) {
                $query->where('is_completed', true);
            }])
            ->where('status', 'completed')
            ->where('updated_at', '>=', $thirtyDaysAgo)
            ->latest('updated_at')
            ->get();

        $totalTasks = $completedTasks->count();
        $totalSubtasks = $completedTasks->sum(fn($task) => $task->subtasks->count());

        // Agrupamos por categoría para crear el gráfico
        $categories = $completedTasks->groupBy('category')->map(function ($tasks) use ($totalTasks) {
            $count = $tasks->count();
            return [
                'count' => $count,
                'percentage' => $totalTasks > 0 ? round(($count / $totalTasks) * 100) : 0
            ];
        });

        // Renderizamos la vista de estadísticas con Inertia (librería para SPA en Laravel)
        return Inertia::render('Stats/Index', [
            'completedTasks' => $completedTasks,
            'stats' => [
                'totalTasks' => $totalTasks,
                'totalSubtasks' => $totalSubtasks,
                'categories' => $categories
            ]
        ]);
    }
}
