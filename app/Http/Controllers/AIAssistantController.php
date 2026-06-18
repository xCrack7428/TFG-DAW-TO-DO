<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;

class AIAssistantController extends Controller
{
    public function analyzeTasks(Request $request) 
    {
        try {
            $user = Auth::user();
            $hoy = now()->toDateString();
            
            $tab = $request->input('tab', 'Hoy');
            $query = Task::where('user_id', $user->id)->where('status', 'pending');

            $contextoTexto = "";
            if ($tab === 'Hoy') {
                $query->whereDate('due_date', $hoy);
                $contextoTexto = "para hoy";
            } elseif ($tab === 'Próximas Tareas') {
                $query->whereDate('due_date', '>', $hoy);
                $contextoTexto = "para los próximos días";
            } else {
                $contextoTexto = "en tu bandeja general";
            }

            $pendingTasks = $query->get();

            if ($pendingTasks->isEmpty()) {
                $tareasActuales = "Ninguna.";
            } else {
                // AQUÍ LE PASAMOS LA PRIORIDAD JUNTO AL TÍTULO A LA IA
                $tareasActuales = $pendingTasks->map(function ($task) {
                    return "'" . $task->title . "' (Prioridad: " . $task->priority . ")";
                })->implode(', ');
            }

            // EL PROMPT para el comportamiento de la IA, incluyendo las reglas estrictas y el contexto de las tareas actuales
            $prompt = "Eres un coach de productividad para la app DAW TO DO. El usuario está en la sección '$tab' y sus tareas pendientes $contextoTexto son: $tareasActuales. 
            REGLAS ESTRICTAS:
            1. Responde ÚNICAMENTE en formato JSON. Nada de texto fuera del JSON.
            2. El JSON debe tener exactamente esta estructura: {\"mensaje\": \"tu consejo aquí\", \"tarea_sugerida\": \"título de tarea o null\", \"categoria\": \"Personal/Estudios/Trabajo/Otros\"}
            3. En 'mensaje', da un consejo directo y conversacional usando emojis.
            4. REGLA DE PRIORIDADES: Si NO vas a sugerir una tarea nueva, analiza las tareas actuales del usuario y recomiéndale por cuál empezar siguiendo este orden exacto: PRIMERO busca si hay alguna con Prioridad 'Alta' y dile que empiece por esa. Si no hay altas, busca una 'Media'. Si tampoco hay, elige una 'Baja'. Explícale brevemente por qué empezar por esa.
            5. A veces (aleatoriamente, 50% de las veces), sugiere una tarea nueva positiva que el usuario no tenga (ej: 'Leer 10 páginas', 'Beber agua'). Si quieres sugerirla, pon el título en 'tarea_sugerida' y su categoría en 'categoria'. En tu 'mensaje' pregúntale si quiere que se la añadas (ignorando la regla 4).
            6. Si no vas a sugerir ninguna tarea, asegúrate de que el valor de 'tarea_sugerida' sea null (el valor nulo de JSON, nunca la palabra \"null\" escrita).";

            // LLAMADA A LA API DE GROQ
            $response = Http::withoutVerifying()
                ->withToken(env('GROQ_API_KEY'))
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.8, 
                ]);

                // Manejo de errores de la respuesta de la API
            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ERROR DE GROQ: ' . $response->body()
                ], 500);
            }

            $aiData = json_decode($response->json()['choices'][0]['message']['content'], true);

            // Validación de la estructura del JSON recibido
            return response()->json([
                'success' => true,
                'message' => $aiData['mensaje'] ?? 'Aquí tienes tu consejo.',
                'suggested_task' => $aiData['tarea_sugerida'] ?? null,
                'suggested_category' => $aiData['categoria'] ?? 'Personal'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ERROR EN LARAVEL: ' . $e->getMessage()
            ], 500);
        }
    }
}