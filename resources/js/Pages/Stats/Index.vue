<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    completedTasks: Array,
    stats: Object
});

// Datos del usuario para el perfil
const user = usePage().props.auth.user;
const userInitials = computed(() => {
    if (!user.name) return 'U';
    const names = user.name.split(' ');
    if (names.length >= 2) return `${names[0][0]}${names[1][0]}`.toUpperCase();
    return names[0].substring(0, 2).toUpperCase();
});

// Colores dinámicos para las barras del gráfico según la categoría
const getCategoryColor = (category) => {
    const colors = {
        'Trabajo': 'bg-blue-500',
        'Personal': 'bg-emerald-500',
        'Estudios': 'bg-purple-500',
        'Otros': 'bg-gray-500'
    };
    return colors[category] || 'bg-indigo-500';
};

// --- LÓGICA DE CABECERA (TEMA, PERFIL Y NOTIFICACIONES) ---
const isProfileMenuOpen = ref(false);
const notifications = ref([]);
const isDarkMode = ref(localStorage.getItem('theme') !== 'light');

// Función para mostrar notificaciones temporales
const showNotification = (message, type = 'success') => {
    const id = Date.now();
    notifications.value.push({ id, message, type });
    setTimeout(() => {
        notifications.value = notifications.value.filter(n => n.id !== id);
    }, 3500);
};

// Función para actualizar el tema según la preferencia del usuario
const updateTheme = () => {
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

// Función para cambiar el tema del usuario
const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
    updateTheme();
    showNotification(isDarkMode.value ? 'Modo Oscuro activado' : 'Modo Claro activado', 'success');
};

onMounted(() => {
    updateTheme();
});
</script>

<template>
    <Head title="Estadísticas - DAW TO DO" />

    <div class="flex h-screen bg-gray-50 dark:bg-[#13131A] text-gray-800 dark:text-gray-200 font-sans relative transition-colors duration-300">
        
        <aside class="w-64 bg-white dark:bg-[#181820] border-r border-gray-200 dark:border-gray-800 flex flex-col hidden md:flex transition-colors duration-300 relative z-10">
            <div class="flex-1 flex flex-col overflow-y-auto">
                <div class="h-20 flex-shrink-0 flex items-center px-6 border-b border-gray-200 dark:border-gray-800">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="text-indigo-600 dark:text-indigo-500">✓</span> DAW TO DO
                    </h1>
                </div>

                <nav class="p-4 space-y-2 mt-4 flex-shrink-0">
                    <Link :href="route('dashboard')" class="w-full flex items-center gap-3 px-4 py-2.5 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-all">
                        <span class="text-lg">⬅️</span> Volver al Dashboard
                    </Link>
                </nav>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-gray-800 space-y-2 flex-shrink-0">
                <Link :href="route('stats.index')" class="flex items-center gap-3 px-4 py-2 bg-indigo-50 dark:bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 border-l-2 border-indigo-600 dark:border-indigo-500 font-semibold rounded-lg transition-colors">
                    📊 Estadísticas
                </Link>
                <Link :href="route('profile.edit')" class="flex items-center gap-3 px-4 py-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    ⚙️ Ajustes
                </Link>
            </div>
        </aside>

        <main class="flex-1 flex flex-col overflow-hidden">
            
            <header class="h-20 bg-white dark:bg-[#13131A] border-b border-gray-200 dark:border-gray-800 flex items-center justify-between px-6 md:px-10 transition-colors duration-300 relative z-20">
                
                <div class="w-full max-w-sm hidden sm:block"></div>

                <div class="flex items-center gap-4 ml-auto">
                    <button @click="toggleTheme" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-all duration-300 mr-2" title="Cambiar tema">
                        <svg v-if="isDarkMode" class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.243 16.243l.707.707M7.757 7.757l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                        </svg>
                        <svg v-else class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <div class="relative z-50">
                        <button @click="isProfileMenuOpen = !isProfileMenuOpen" class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-sm text-white shadow-lg hover:shadow-indigo-500/30 transition-all transform hover:scale-105 focus:outline-none relative z-50">
                            {{ userInitials }}
                        </button>

                        <transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="transform opacity-0 scale-95 -translate-y-2"
                            enter-to-class="transform opacity-100 scale-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="transform opacity-100 scale-100 translate-y-0"
                            leave-to-class="transform opacity-0 scale-95 -translate-y-2"
                        >
                            <div v-if="isProfileMenuOpen" class="absolute right-0 mt-3 w-56 bg-white dark:bg-[#181820] rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 py-1.5 z-50 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700/50 mb-1 bg-gray-50/50 dark:bg-[#13131A]/50">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ user.email }}</p>
                                </div>
                                
                                <Link :href="route('profile.edit')" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    ⚙️ Ajustes de Cuenta
                                </Link>
                                
                                <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors text-left border-t border-gray-100 dark:border-gray-700/50 mt-1">
                                    🚪 Cerrar Sesión
                                </Link>
                            </div>
                        </transition>
                    </div>

                    <transition
                        enter-active-class="transition-opacity ease-linear duration-300"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition-opacity ease-linear duration-200"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <div v-if="isProfileMenuOpen" @click="isProfileMenuOpen = false" class="fixed inset-0 z-40 bg-gray-900/10 dark:bg-black/30 backdrop-blur-[2px]"></div>
                    </transition>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 md:p-10 relative z-0">
                
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Tu Resumen Mensual 🏆</h2>
                    <p class="text-gray-500">Un vistazo a todo lo que has logrado en los últimos 30 días.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 shadow-lg shadow-indigo-500/20 text-white relative overflow-hidden">
                        <div class="absolute right-0 top-0 -mt-4 -mr-4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                        <h4 class="text-indigo-100 font-medium text-sm uppercase tracking-wider mb-2 relative z-10">Tareas Completadas</h4>
                        <div class="text-5xl font-bold relative z-10">{{ stats.totalTasks }}</div>
                    </div>
                    <div class="bg-white dark:bg-[#181820] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                        <h4 class="text-gray-500 dark:text-gray-400 font-medium text-sm uppercase tracking-wider mb-2">Subtareas Finalizadas</h4>
                        <div class="text-5xl font-bold text-gray-900 dark:text-white">{{ stats.totalSubtasks }}</div>
                    </div>
                </div>

                <div v-if="stats.totalTasks > 0" class="bg-white dark:bg-[#181820] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm mb-10">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Distribución por Categorías</h3>
                    <div class="space-y-5">
                        <div v-for="(data, category) in stats.categories" :key="category">
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ category || 'Otros' }}</span>
                                <span class="text-xs text-gray-500">{{ data.count }} tareas ({{ data.percentage }}%)</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-[#13131A] rounded-full h-3 border border-gray-200 dark:border-gray-800/50 overflow-hidden">
                                <div :class="['h-3 rounded-full transition-all duration-1000 ease-out', getCategoryColor(category)]" 
                                     :style="{ width: data.percentage + '%' }">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Historial de Éxitos</h3>
                    
                    <div v-if="completedTasks.length === 0" class="text-center py-12 bg-white dark:bg-[#181820] rounded-2xl border border-gray-200 dark:border-gray-800">
                        <span class="text-4xl mb-4 block opacity-50">🌱</span>
                        <p class="text-gray-500">Aún no has completado tareas este mes. ¡Empieza hoy mismo!</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div v-for="task in completedTasks" :key="task.id" class="bg-white dark:bg-[#181820] border border-gray-200 dark:border-gray-800 rounded-xl p-5 flex flex-col sm:flex-row gap-4 justify-between sm:items-center hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors shadow-sm">
                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white line-through opacity-80 text-lg">{{ task.title }}</h4>
                                    <div class="flex flex-wrap items-center gap-3 mt-2">
                                        <span class="text-xs font-semibold px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-md">
                                            {{ task.category || 'Otros' }}
                                        </span>
                                        <span v-if="task.subtasks && task.subtasks.length > 0" class="text-xs text-indigo-600 dark:text-indigo-400 font-medium flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                            {{ task.subtasks.length }} subtareas completadas
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 hidden sm:block whitespace-nowrap">
                                {{ new Date(task.updated_at).toLocaleDateString() }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <div class="fixed bottom-6 right-6 z-[60] flex flex-col gap-3 pointer-events-none">
            <div v-for="notif in notifications" :key="notif.id" 
                class="transform transition-all duration-300 flex items-center gap-3 px-5 py-3 rounded-xl shadow-2xl text-sm font-medium text-white min-w-[250px]"
                :class="{
                    'bg-indigo-600': notif.type === 'success',
                    'bg-red-500': notif.type === 'error',
                    'bg-amber-500 text-amber-950': notif.type === 'warning'
                }">
                <span v-if="notif.type === 'success'" class="text-xl">✅</span>
                <span v-if="notif.type === 'error'" class="text-xl">🗑️</span>
                <span v-if="notif.type === 'warning'" class="text-xl">⚠️</span>
                {{ notif.message }}
            </div>
        </div>

    </div>
</template>