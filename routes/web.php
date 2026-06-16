<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController; // Importamos nuestro controlador de tareas
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\AIAssistantController; // Importamos el controlador del Asistente IA

// Ruta de inicio
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Rutas de Tareas
Route::get('/dashboard', [TaskController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::post('/tasks', [TaskController::class, 'store'])
    ->middleware('auth')
    ->name('tasks.store');

Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggleStatus'])
    ->middleware('auth')
    ->name('tasks.toggle');

Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])
    ->middleware('auth')
    ->name('tasks.destroy');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->middleware('auth')->name('tasks.update');
});

// Rutas de Reseñas
Route::get('/reviews', [ReviewController::class, 'index'])->middleware('auth')->name('reviews.index');
Route::post('/reviews', [ReviewController::class, 'store'])->middleware('auth')->name('reviews.store');

// Rutas de Autenticación Social
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');



Route::middleware('auth')->group(function () {

    // Ruta del Asistente IA
    Route::post('/ai/analyze', [AIAssistantController::class, 'analyzeTasks'])->name('ai.analyze');
});

require __DIR__ . '/auth.php';
