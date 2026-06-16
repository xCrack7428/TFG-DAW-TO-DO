<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Task; // Asegúrate de que este es tu modelo de tareas

class AIAssistantController extends Controller
{
    public function analyzeTasks()
    {
        $user = Auth::user();

        // Buscamos las tareas pendientes del usuario logueado.
        // ADAPTA ESTO a tu base de datos: asumimos que hay un campo 'is_completed' o 'status'
        $pendingTasks = Task::where('user_id', $user->id)
            ->where('status', 'pending') // Cambia 'status' por el nombre de tu columna
            ->get();

        // 1. Preparamos el Prompt dependiendo de si hay tareas o no
        if ($pendingTasks->isEmpty()) {
            $prompt = "Actúa como el asistente virtual oficial de una aplicación de productividad llamada 'DAW TO DO'. El usuario actual no tiene ninguna tarea pendiente registrada. Empieza saludando exactamente con: 'Bienvenido a DAW TO DO, vamos a empezar a gestionar tus tareas'. Después, anímale brevemente en un par de frases a crear su primera tarea y explícale un beneficio rápido de organizar su día. Sé cercano, motivador y usa algún emoji.";
        } else {
            // Extraemos solo los títulos de las tareas
            $taskNames = $pendingTasks->pluck('title')->implode(', ');

            $prompt = "Actúa como un coach de productividad para la aplicación 'DAW TO DO'. El usuario tiene estas tareas pendientes hoy: " . $taskNames . ". Dale un consejo breve y motivador sobre por cuál empezar (por ejemplo, la más difícil primero o agrupar similares) y cómo organizar su tiempo. Sé conciso, directo, usa algún emoji y no hagas listas largas.";
        }

        // 2. Hacemos la llamada a la API de Gemini (Versión 1.5 Flash)
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . env('GEMINI_API_KEY'), [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            // 3. Extraemos el texto de la respuesta
            $aiText = $response->json()['candidates'][0]['content']['parts'][0]['text'];

            return response()->json([
                'success' => true,
                'message' => $aiText
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vaya, parece que el asistente IA está durmiendo. Inténtalo de nuevo más tarde.'
            ], 500);
        }
    }
}
