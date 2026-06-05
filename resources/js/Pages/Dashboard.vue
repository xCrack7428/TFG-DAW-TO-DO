<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    tasks: Array
});

// --- OBTENER DATOS DEL USUARIO REAL ---
const user = usePage().props.auth.user;
const userInitials = computed(() => {
    if (!user.name) return 'U';
    const names = user.name.split(' ');
    if (names.length >= 2) {
        return `${names[0][0]}${names[1][0]}`.toUpperCase();
    }
    return names[0].substring(0, 2).toUpperCase();
});

const isModalOpen = ref(false);
const searchQuery = ref('');

// Controla el estado del menú desplegable del perfil
const isProfileMenuOpen = ref(false);

// --- LEER AJUSTES GUARDADOS DEL LOCALSTORAGE ---
const activeTab = ref(localStorage.getItem('defaultStartupView') || 'Hoy');
const listDensity = ref(localStorage.getItem('listDensity') || 'Cómoda');

const editingTask = ref(null); 

const todayDateString = new Date().toISOString().split('T')[0];
const notifications = ref([]);

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

const showNotification = (message, type = 'success') => {
    const id = Date.now();
    notifications.value.push({ id, message, type });
    
    setTimeout(() => {
        notifications.value = notifications.value.filter(n => n.id !== id);
    }, 3500);
};

onMounted(() => {
    updateTheme(); // Aplicamos el tema guardado nada más arrancar

    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowDateString = tomorrow.toISOString().split('T')[0];

    const expiringTasks = props.tasks.filter(task => 
        task.status !== 'completed' && 
        (task.due_date === todayDateString || task.due_date === tomorrowDateString)
    );

    if (expiringTasks.length > 0) {
        setTimeout(() => {
            showNotification(`¡Atención! Tienes ${expiringTasks.length} tarea(s) que vencen hoy o mañana.`, 'warning');
        }, 1000);
    }
});

const filteredTasks = computed(() => {
    let tabTasks = props.tasks;
    
    if (activeTab.value === 'Hoy') {
        tabTasks = props.tasks.filter(task => task.due_date === todayDateString);
    } else if (activeTab.value === 'Próximas Tareas') {
        tabTasks = props.tasks.filter(task => task.due_date > todayDateString);
    }

    if (!searchQuery.value) {
        return tabTasks;
    }
    const lowerCaseQuery = searchQuery.value.toLowerCase();
    return tabTasks.filter(task => 
        task.title.toLowerCase().includes(lowerCaseQuery) || 
        (task.description && task.description.toLowerCase().includes(lowerCaseQuery))
    );
});

const totalTasks = computed(() => filteredTasks.value.length);
const completedTasks = computed(() => 
    filteredTasks.value.filter(task => task.status === 'completed').length
);
const progressPercentage = computed(() => {
    if (totalTasks.value === 0) return 0;
    return Math.round((completedTasks.value / totalTasks.value) * 100);
});

const form = useForm({
    title: '',
    description: '',
    due_date: todayDateString,
    priority: 'Media',
    category: 'Personal', 
});

const openCreateModal = () => {
    editingTask.value = null;
    form.clearErrors();
    form.title = '';
    form.description = '';
    form.due_date = todayDateString;
    form.priority = 'Media';
    form.category = 'Personal';
    isModalOpen.value = true;
};

const openEditModal = (task) => {
    editingTask.value = task;
    form.title = task.title;
    form.description = task.description || '';
    form.due_date = task.due_date || '';
    form.priority = task.priority;
    form.category = task.category || 'Personal';
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        form.clearErrors();
        form.title = '';
        form.description = '';
        form.due_date = todayDateString;
        form.priority = 'Media';
        form.category = 'Personal';
        editingTask.value = null;
    }, 200); 
};

const submitTask = () => {
    if (editingTask.value) {
        form.put(route('tasks.update', editingTask.value.id), {
            onSuccess: () => {
                closeModal();
                showNotification('Tarea actualizada con éxito', 'success');
            }
        });
    } else {
        form.post(route('tasks.store'), {
            onSuccess: () => {
                closeModal();
                showNotification('¡Nueva tarea creada!', 'success');
            }
        });
    }
};

const toggleTask = (taskId) => {
    router.patch(route('tasks.toggle', taskId), {}, { 
        preserveScroll: true,
        onSuccess: () => showNotification('Estado modificado', 'success')
    });
};

const deleteTask = (taskId) => {
    if (confirm('¿Estás seguro de que deseas eliminar esta tarea? Esta acción no se puede deshacer.')) {
        router.delete(route('tasks.destroy', taskId), { 
            preserveScroll: true,
            onSuccess: () => showNotification('Tarea eliminada permanentemente', 'error')
        });
    }
};
</script>

<template>
    <Head title="DAW TO DO" />

    <div class="flex h-screen bg-gray-50 dark:bg-[#13131A] text-gray-800 dark:text-gray-200 font-sans relative transition-colors duration-300">
        
        <aside class="w-64 bg-white dark:bg-[#181820] border-r border-gray-200 dark:border-gray-800 flex flex-col justify-between hidden md:flex transition-colors duration-300 relative z-10">
            <div>
                <div class="h-20 flex items-center px-6 border-b border-gray-200 dark:border-gray-800">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="text-indigo-600 dark:text-indigo-500">✓</span> DAW TO DO
                    </h1>
                </div>

                <nav class="p-4 space-y-2 mt-4">
                    <button @click="openCreateModal" class="w-full flex items-center justify-center gap-3 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors mb-6 shadow-lg shadow-indigo-600/10 dark:shadow-indigo-900/20">
                        <span>+ Nueva Tarea</span>
                    </button>

                    <div class="space-y-1">
                        <button @click="activeTab = 'Bandeja'" :class="['w-full flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all', activeTab === 'Bandeja' ? 'bg-indigo-50 dark:bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 border-l-2 border-indigo-600 dark:border-indigo-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 border-l-2 border-transparent']">
                            <span class="text-lg">📥</span> Bandeja
                        </button>
                        <button @click="activeTab = 'Hoy'" :class="['w-full flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all', activeTab === 'Hoy' ? 'bg-indigo-50 dark:bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 border-l-2 border-indigo-600 dark:border-indigo-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 border-l-2 border-transparent']">
                            <span class="text-lg">☀️</span> Hoy
                        </button>
                        <button @click="activeTab = 'Próximas Tareas'" :class="['w-full flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all', activeTab === 'Próximas Tareas' ? 'bg-indigo-50 dark:bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 border-l-2 border-indigo-600 dark:border-indigo-500 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 border-l-2 border-transparent']">
                            <span class="text-lg">📅</span> Próximas Tareas
                        </button>
                    </div>
                </nav>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-gray-800">
                <Link :href="route('profile.edit')" class="flex items-center gap-3 px-4 py-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">⚙️ Ajustes</Link>
            </div>
        </aside>

        <main class="flex-1 flex flex-col overflow-hidden">
            <header class="h-20 bg-white dark:bg-[#13131A] border-b border-gray-200 dark:border-gray-800 flex items-center justify-between px-6 md:px-10 transition-colors duration-300 relative z-20">
                <div class="w-full max-w-sm hidden sm:block">
                    <input v-model="searchQuery" type="text" placeholder="Buscar tareas..." class="w-full bg-gray-100 dark:bg-[#181820] border border-gray-300 dark:border-gray-700 text-sm rounded-md px-4 py-2 focus:outline-none focus:border-indigo-500 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-colors">
                </div>
                
                <div class="flex items-center gap-4 ml-auto">
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

                    <!-- EFECTO BLUR PARA EL FONDO CUANDO EL MENÚ ESTÁ ABIERTO -->
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
                
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6 mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ activeTab }}</h2>
                        <p class="text-gray-500" v-if="activeTab === 'Hoy'">Tus tareas para hoy</p>
                        <p class="text-gray-500" v-if="activeTab === 'Bandeja'">Todas tus tareas guardadas</p>
                        <p class="text-gray-500" v-if="activeTab === 'Próximas Tareas'">Tareas futuras en tu agenda</p>
                    </div>
                    
                    <div class="bg-white dark:bg-[#181820] border border-gray-200 dark:border-gray-800 rounded-xl p-4 w-full sm:w-64 shadow-md dark:shadow-xl transition-colors duration-300">
                        <div class="flex justify-between items-end mb-2">
                            <span class="text-xs font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Progreso</span>
                            <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ completedTasks }}/{{ totalTasks }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-[#13131A] rounded-full h-2 border border-gray-200 dark:border-gray-800/50 overflow-hidden">
                            <div class="bg-indigo-500 h-2 rounded-full transition-all duration-700 ease-out" 
                                 :style="{ width: progressPercentage + '%' }">
                            </div>
                        </div>
                    </div>

                    <button @click="openCreateModal" class="md:hidden bg-indigo-600 text-white p-3 rounded-full shadow-lg absolute top-6 right-6 z-10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                </div>

                <div v-if="filteredTasks.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="text-7xl mb-6 opacity-80">{{ searchQuery ? '🔍' : (activeTab === 'Hoy' ? '☀️' : '🧘‍♂️') }}</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ searchQuery ? 'No se encontraron tareas' : 'Nada por aquí' }}
                    </h3>
                    <p class="text-gray-500 max-w-sm">
                        {{ searchQuery ? 'Prueba con palabras diferentes.' : `No tienes tareas en la sección de ${activeTab}. ¡Disfruta de tu tiempo o crea una nueva!` }}
                    </p>
                </div>

                <div v-else class="space-y-3">
                    <div v-for="task in filteredTasks" :key="task.id" 
                        :class="[
                            'bg-white dark:bg-[#181820] border border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700 transition-all duration-200 rounded-xl flex items-start group shadow-sm hover:shadow-md dark:hover:shadow-black/20',
                            listDensity === 'Compacta' ? 'p-3 gap-3' : 'p-5 gap-4'
                        ]">
                        
                        <button @click="toggleTask(task.id)" 
                            :class="[
                                'rounded-full border-2 transition-all flex-shrink-0 flex items-center justify-center cursor-pointer', 
                                listDensity === 'Compacta' ? 'w-4 h-4 mt-0.5' : 'w-5 h-5 mt-1',
                                task.status === 'completed' ? 'bg-indigo-500 border-indigo-500 scale-105' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-500'
                            ]">
                            <svg v-if="task.status === 'completed'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </button>

                        <div class="flex-1 min-w-0">
                            <h4 :class="[
                                'font-medium transition-all duration-300 truncate', 
                                listDensity === 'Compacta' ? 'text-base' : 'text-lg',
                                task.status === 'completed' ? 'text-gray-400 dark:text-gray-600 line-through' : 'text-gray-800 dark:text-gray-200'
                            ]">
                                {{ task.title }}
                            </h4>
                            
                            <p v-if="task.description" :class="[
                                'line-clamp-2 transition-all duration-300', 
                                listDensity === 'Compacta' ? 'text-xs mt-0.5' : 'text-sm mt-1',
                                task.status === 'completed' ? 'text-gray-400 dark:text-gray-700' : 'text-gray-600 dark:text-gray-500'
                            ]">
                                {{ task.description }}
                            </p>

                            <div :class="['flex flex-wrap items-center gap-3 text-xs font-medium transition-opacity duration-300', listDensity === 'Compacta' ? 'mt-2' : 'mt-4', task.status === 'completed' ? 'opacity-50' : '']">
                                
                                <span :class="{
                                    'text-red-700 bg-red-50 dark:text-red-400 dark:bg-red-400/10 border-red-200 dark:border-red-400/20': task.priority === 'Alta',
                                    'text-amber-700 bg-amber-50 dark:text-yellow-400 dark:bg-yellow-400/10 border-amber-200 dark:border-yellow-400/20': task.priority === 'Media',
                                    'text-indigo-700 bg-indigo-50 dark:text-indigo-400 dark:bg-indigo-400/10 border-indigo-200 dark:border-indigo-400/20': task.priority === 'Baja',
                                }" class="px-2.5 py-1 rounded-md border flex items-center gap-1.5">
                                    <div :class="{
                                        'bg-red-500': task.priority === 'Alta',
                                        'bg-amber-500 dark:bg-yellow-400': task.priority === 'Media',
                                        'bg-indigo-500': task.priority === 'Baja',
                                    }" class="w-1.5 h-1.5 rounded-full"></div>
                                    {{ task.priority }}
                                </span>

                                <span v-if="task.category" :class="{
                                    'text-blue-700 bg-blue-50 dark:text-blue-400 dark:bg-blue-400/10 border-blue-200 dark:border-blue-400/20': task.category === 'Trabajo',
                                    'text-emerald-700 bg-emerald-50 dark:text-emerald-400 dark:bg-emerald-400/10 border-emerald-200 dark:border-emerald-400/20': task.category === 'Personal',
                                    'text-purple-700 bg-purple-50 dark:text-purple-400 dark:bg-purple-400/10 border-purple-200 dark:border-purple-400/20': task.category === 'Estudios',
                                    'text-gray-600 bg-gray-100 dark:text-gray-400 dark:bg-gray-400/10 border-gray-200 dark:border-gray-400/20': task.category === 'Otros' || !['Trabajo', 'Personal', 'Estudios'].includes(task.category),
                                }" class="px-2.5 py-1 rounded-md border flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                    {{ task.category || 'Otros' }}
                                </span>

                                <span v-if="task.due_date" class="text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-[#13131A] px-2.5 py-1 rounded-md border border-gray-200 dark:border-gray-800 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    {{ task.due_date }}
                                </span>
                            </div>
                        </div>

                        <div class="opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                            <button @click="openEditModal(task)" :class="['text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-400/10 rounded-lg transition-colors', listDensity === 'Compacta' ? 'p-1.5' : 'p-2.5']" title="Editar tarea">
                                <svg :class="listDensity === 'Compacta' ? 'w-4 h-4' : 'w-5 h-5'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <button @click="deleteTask(task.id)" :class="['text-gray-400 dark:text-gray-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-400/10 rounded-lg transition-colors', listDensity === 'Compacta' ? 'p-1.5' : 'p-2.5']" title="Eliminar tarea">
                                <svg :class="listDensity === 'Compacta' ? 'w-4 h-4' : 'w-5 h-5'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
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

    <div v-if="isModalOpen" class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-all">
        <div class="bg-white dark:bg-[#181820] border border-gray-200 dark:border-gray-700 rounded-2xl w-full max-w-lg p-7 shadow-2xl transition-colors duration-300">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ editingTask ? 'Editar Tarea' : 'Nueva Tarea' }}</h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-900 dark:hover:text-white p-1 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form @submit.prevent="submitTask" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">Título de la tarea</label>
                    <input v-model="form.title" type="text" placeholder="¿Qué hay que hacer?" required
                        class="w-full bg-gray-50 dark:bg-[#13131A] border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">Descripción</label>
                    <textarea v-model="form.description" rows="3" placeholder="Añade notas o detalles..."
                        class="w-full bg-gray-50 dark:bg-[#13131A] border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">Vencimiento</label>
                        <input v-model="form.due_date" type="date"
                            class="w-full bg-gray-50 dark:bg-[#13131A] border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all color-scheme-light dark:color-scheme-dark">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">Prioridad</label>
                        <select v-model="form.priority" class="w-full bg-gray-50 dark:bg-[#13131A] border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                            <option value="Alta">Alta</option>
                            <option value="Media">Media</option>
                            <option value="Baja">Baja</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wider">Categoría</label>
                        <select v-model="form.category" class="w-full bg-gray-50 dark:bg-[#13131A] border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                            <option value="Trabajo">💼 Trabajo</option>
                            <option value="Personal">🏠 Personal</option>
                            <option value="Estudios">📚 Estudios</option>
                            <option value="Otros">📌 Otros</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <button type="button" @click="closeModal" class="px-5 py-2.5 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2.5 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors shadow-lg shadow-indigo-600/10 dark:shadow-indigo-900/20 disabled:opacity-50">
                        {{ form.processing ? 'Guardando...' : (editingTask ? 'Actualizar Tarea' : 'Guardar Tarea') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style>
/* Forzar que el calendario del input de fecha se invierta solo en modo oscuro */
.dark input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
}
input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.6;
}
input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}
</style>