<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

// Variables para los ajustes locales (Visualización)
const defaultStartupView = ref('Hoy');
const listDensity = ref('Cómoda');
const showSuccessMessage = ref(false);

const user = usePage().props.auth.user;
const isProfileMenuOpen = ref(false); // Control del menú

// --- SISTEMA DE NOTIFICACIONES ---
const notifications = ref([]);

const showNotification = (message, type = 'success') => {
    const id = Date.now();
    notifications.value.push({ id, message, type });
    
    setTimeout(() => {
        notifications.value = notifications.value.filter(n => n.id !== id);
    }, 3500);
};

// Generar iniciales del usuario para el avatar
const userInitials = computed(() => {
    if (!user.name) return 'U';
    const names = user.name.split(' ');
    if (names.length >= 2) {
        return `${names[0][0]}${names[1][0]}`.toUpperCase();
    }
    return names[0].substring(0, 2).toUpperCase();
});

// --- LÓGICA DE MODO CLARO / OSCURO INTERACTIVO ---
const isDarkMode = ref(localStorage.getItem('theme') !== 'light');

const updateTheme = () => {
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
    updateTheme();
    showNotification(isDarkMode.value ? 'Modo Oscuro activado' : 'Modo Claro activado', 'success');
};

onMounted(() => {
    updateTheme(); 
    
    // Cargar los ajustes guardados previamente
    const savedView = localStorage.getItem('defaultStartupView');
    const savedDensity = localStorage.getItem('listDensity');
    
    if (savedView) defaultStartupView.value = savedView;
    if (savedDensity) listDensity.value = savedDensity;
});

const saveSettings = () => {
    localStorage.setItem('defaultStartupView', defaultStartupView.value);
    localStorage.setItem('listDensity', listDensity.value);
    
    showSuccessMessage.value = true;
    setTimeout(() => {
        showSuccessMessage.value = false;
    }, 3000);
};
</script>

<template>
    <Head title="Ajustes - DAW TO DO" />

    <div class="flex h-screen bg-gray-50 dark:bg-[#13131A] text-gray-800 dark:text-gray-200 font-sans transition-colors duration-300">
        
        <aside class="w-64 bg-white dark:bg-[#181820] border-r border-gray-200 dark:border-gray-800 flex flex-col hidden md:flex transition-colors duration-300 relative z-10">
            
            <div class="flex-1 flex flex-col overflow-y-auto">
                <div class="h-20 flex-shrink-0 flex items-center px-6 border-b border-gray-200 dark:border-gray-800">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="text-indigo-600 dark:text-indigo-500">✓</span> DAW TO DO
                    </h1>
                </div>

                <nav class="p-4 space-y-2 mt-4 flex-shrink-0">
                    <Link :href="route('dashboard')" class="w-full flex items-center gap-3 px-4 py-2.5 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors border-l-2 border-transparent">
                        <span class="text-lg">⬅️</span> Volver al Dashboard
                    </Link>
                </nav>

                <!-- CARTEL DE NOVEDADES AMPLIADO -->
                <div class="mt-auto mx-4 mb-4 p-5 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/10 dark:to-purple-900/10 rounded-xl border border-indigo-100 dark:border-indigo-800/30 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xl">🚀</span>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">Versión 2.0</h4>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-4 leading-relaxed">
                        Nuestro equipo está trabajando en las siguientes características para la próxima gran actualización:
                    </p>
                    <ul class="space-y-2 mb-4">
                        <li class="flex items-start gap-2 text-xs text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Modo colaborativo
                        </li>
                        <li class="flex items-start gap-2 text-xs text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Sincronización Offline
                        </li>
                        <li class="flex items-start gap-2 text-xs text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Notificaciones Push
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Botón de Comunidad y Ajustes -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-800 space-y-2 flex-shrink-0">
                <Link :href="route('reviews.index')" class="flex items-center gap-3 px-4 py-2 text-gray-500 dark:text-gray-400 hover:text-yellow-600 dark:hover:text-yellow-500 hover:bg-yellow-50 dark:hover:bg-yellow-900/10 rounded-lg transition-colors">
                    ⭐ Comunidad
                </Link>
                <div class="flex items-center gap-3 px-4 py-2 bg-indigo-50 dark:bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 border-l-2 border-indigo-600 dark:border-indigo-500 rounded-lg font-semibold">
                    ⚙️ Ajustes
                </div>
            </div>
        </aside>

        <main class="flex-1 flex flex-col overflow-hidden relative">
            
            <div :class="['absolute top-6 left-1/2 transform -translate-x-1/2 bg-emerald-500 text-white px-6 py-3 rounded-xl shadow-lg transition-all duration-300 flex items-center gap-2 z-50', showSuccessMessage ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-4 pointer-events-none']">
                <span>✅</span> Preferencias visuales guardadas
            </div>

            <header class="h-20 bg-white dark:bg-[#13131A] border-b border-gray-200 dark:border-gray-800 flex items-center justify-end px-6 md:px-10 transition-colors duration-300 relative z-20">
                <div class="flex items-center gap-4">
                    
                    <!-- BOTÓN MODO CLARO / OSCURO -->
                    <button @click="toggleTheme" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-all duration-300 mr-2" title="Cambiar tema">
                        <svg v-if="isDarkMode" class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.243 16.243l.707.707M7.757 7.757l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                        </svg>
                        <svg v-else class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- MENÚ DESPLEGABLE DEL PERFIL -->
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

                    <!-- Capa invisible con desenfoque (Backdrop Blur) -->
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
                <div class="max-w-4xl mx-auto space-y-8">
                    
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Ajustes de Cuenta</h2>
                        <p class="text-gray-500">Gestiona tus datos personales y tus preferencias del espacio de trabajo.</p>
                    </div>

                    <!-- BLOQUE 1: PERFIL REAL (Base de datos) -->
                    <div class="p-8 bg-white dark:bg-[#181820] shadow-sm dark:shadow-xl sm:rounded-2xl border border-gray-200 dark:border-gray-800 transition-colors duration-300">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-xl dark-theme-form"
                        />
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
                        
                        <!-- BLOQUE 2: APARIENCIA Y DENSIDAD -->
                        <div class="p-8 bg-white dark:bg-[#181820] shadow-sm dark:shadow-xl sm:rounded-2xl border border-gray-200 dark:border-gray-800 transition-colors duration-300">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Apariencia Visual</h3>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Densidad de la lista</label>
                                    <p class="text-xs text-gray-500 mb-3">Elige cuánto espacio quieres entre las tareas.</p>
                                    <select v-model="listDensity" class="w-full bg-gray-50 dark:bg-[#13131A] border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                                        <option value="Cómoda">Cómoda (Más espacio)</option>
                                        <option value="Compacta">Compacta (Más tareas en pantalla)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- BLOQUE 3: CONFIGURACIÓN DEL WORKSPACE -->
                        <div class="p-8 bg-white dark:bg-[#181820] shadow-sm dark:shadow-xl sm:rounded-2xl border border-gray-200 dark:border-gray-800 transition-colors duration-300">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Arranque de la App</h3>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vista de inicio por defecto</label>
                                    <p class="text-xs text-gray-500 mb-3">Elige qué pestaña quieres ver al abrir la aplicación.</p>
                                    <select v-model="defaultStartupView" class="w-full bg-gray-50 dark:bg-[#13131A] border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                                        <option value="Bandeja">📥 Bandeja</option>
                                        <option value="Hoy">☀️ Hoy</option>
                                        <option value="Próximas Tareas">📅 Próximas Tareas</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="flex justify-end pt-4">
                        <button @click="saveSettings" class="px-6 py-3 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors shadow-lg shadow-indigo-600/10 dark:shadow-indigo-900/20">
                            Guardar Preferencias Visuales
                        </button>
                    </div>

                    <!-- BLOQUE 4: ACERCA DEL PROYECTO (TFG) -->
                    <div class="mt-12 p-8 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/10 dark:to-purple-900/10 sm:rounded-2xl border border-indigo-100 dark:border-indigo-800/30 transition-colors duration-300">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center text-white text-xl font-bold shadow-lg shadow-indigo-600/30">
                                ✓
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">DAW TO DO</h3>
                                <p class="text-sm text-indigo-600 dark:text-indigo-400 font-medium">Trabajo de Fin de Grado</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mt-6 text-sm">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 mb-1">Desarrollado por</p>
                                <p class="font-semibold text-gray-900 dark:text-white">Mario Moñino Larrosa y Javier Vicente Andrés</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 mb-1">Ciclo Formativo</p>
                                <p class="font-semibold text-gray-900 dark:text-white">Desarrollo de Aplicaciones Web (DAW)</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 mb-1">Versión</p>
                                <p class="font-semibold text-gray-900 dark:text-white">1.0.0</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 mb-1">Año Académico</p>
                                <p class="font-semibold text-gray-900 dark:text-white">2025/2026</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
        
        <!-- CONTENEDOR DE NOTIFICACIONES TOAST -->
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

<style>
/* Forzar estilos oscuros en el componente genérico de Laravel */
.dark .dark-theme-form h2 { color: white !important; }
.dark .dark-theme-form p { color: #9ca3af !important; }
.dark .dark-theme-form label { color: #d1d5db !important; font-size: 0.875rem !important; font-weight: 500 !important; }
.dark .dark-theme-form input { 
    background-color: #13131A !important; 
    border-color: #374151 !important; 
    color: white !important;
}
.dark .dark-theme-form button {
    background-color: #4f46e5 !important;
}
</style>