<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    reviews: Array
});

const user = usePage().props.auth.user;

const userInitials = computed(() => {
    if (!user.name) return 'U';
    const names = user.name.split(' ');
    if (names.length >= 2) return `${names[0][0]}${names[1][0]}`.toUpperCase();
    return names[0].substring(0, 2).toUpperCase();
});

const isProfileMenuOpen = ref(false);
const notifications = ref([]);

const showNotification = (message, type = 'success') => {
    const id = Date.now();
    notifications.value.push({ id, message, type });
    setTimeout(() => {
        notifications.value = notifications.value.filter(n => n.id !== id);
    }, 3500);
};

// MODO CLARO / OSCURO
const isDarkMode = ref(localStorage.getItem('theme') !== 'light');
const updateTheme = () => {
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};
const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
    localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light');
    updateTheme();
    
    // Notificación añadida aquí
    const themeName = isDarkMode.value ? 'Modo Oscuro' : 'Modo Claro';
    showNotification(`✅ ${themeName} activado`, 'success');
};

onMounted(() => { updateTheme(); });

// FORMULARIO DE RESEÑA
const form = useForm({
    rating: 5,
    comment: ''
});

const hoverRating = ref(0);

const submitReview = () => {
    form.post(route('reviews.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('comment', 'rating');
            form.rating = 5;
            showNotification('✅ ¡Reseña publicada con éxito!', 'success');
        }
    });
};

// Formatear la fecha
const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('es-ES', options);
};

// Extraer iniciales para los avatares de las reseñas
const getInitials = (name) => {
    if (!name) return 'U';
    const names = name.split(' ');
    if (names.length >= 2) return `${names[0][0]}${names[1][0]}`.toUpperCase();
    return name.substring(0, 2).toUpperCase();
};
</script>

<template>
    <Head title="Comunidad - DAW TO DO" />

    <div class="flex h-screen bg-gray-50 dark:bg-[#13131A] text-gray-800 dark:text-gray-200 font-sans relative transition-colors duration-300">
        
        <aside class="w-64 bg-white dark:bg-[#181820] border-r border-gray-200 dark:border-gray-800 flex flex-col justify-between hidden md:flex transition-colors duration-300 relative z-10">
            <div>
                <div class="h-20 flex items-center px-6 border-b border-gray-200 dark:border-gray-800">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="text-indigo-600 dark:text-indigo-500">✓</span> DAW TO DO
                    </h1>
                </div>

                <nav class="p-4 space-y-2 mt-4">
                    <Link :href="route('dashboard')" class="w-full flex items-center gap-3 px-4 py-2.5 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors border-l-2 border-transparent">
                        <span class="text-lg">⬅️</span> Volver al Dashboard
                    </Link>
                </nav>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-gray-800 space-y-2">
                <div class="flex items-center gap-3 px-4 py-2 bg-yellow-50 dark:bg-yellow-900/10 text-yellow-600 dark:text-yellow-500 border-l-2 border-yellow-500 rounded-lg font-semibold">
                    ⭐ Comunidad
                </div>
                <Link :href="route('profile.edit')" class="flex items-center gap-3 px-4 py-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    ⚙️ Ajustes
                </Link>
            </div>
        </aside>

        <main class="flex-1 flex flex-col overflow-hidden relative">
            <header class="h-20 bg-white dark:bg-[#13131A] border-b border-gray-200 dark:border-gray-800 flex items-center justify-end px-6 md:px-10 transition-colors duration-300 relative z-20">
                <div class="flex items-center gap-4">
                    <button @click="toggleTheme" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-all duration-300 mr-2">
                        <svg v-if="isDarkMode" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.243 16.243l.707.707M7.757 7.757l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" /></svg>
                        <svg v-else class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                    </button>

                    <div class="relative z-50">
                        <button @click="isProfileMenuOpen = !isProfileMenuOpen" class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-sm text-white shadow-lg hover:scale-105 transition-all">
                            {{ userInitials }}
                        </button>

                        <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                            <div v-if="isProfileMenuOpen" class="absolute right-0 mt-3 w-56 bg-white dark:bg-[#181820] rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 py-1.5 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-[#13131A]/50">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500 truncate mt-0.5">{{ user.email }}</p>
                                </div>
                                <Link :href="route('profile.edit')" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">⚙️ Ajustes</Link>
                                <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 text-left border-t border-gray-100 dark:border-gray-700/50 mt-1">🚪 Cerrar Sesión</Link>
                            </div>
                        </transition>
                    </div>

                    <transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-opacity duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                        <div v-if="isProfileMenuOpen" @click="isProfileMenuOpen = false" class="fixed inset-0 z-40 bg-gray-900/10 dark:bg-black/30 backdrop-blur-[2px]"></div>
                    </transition>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-6 md:p-10 relative z-0">
                <div class="max-w-4xl mx-auto">
                    
                    <div class="mb-10 text-center">
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">Muro de la Comunidad</h2>
                        <p class="text-gray-500 max-w-lg mx-auto">Descubre lo que opinan otros usuarios y comparte tu experiencia usando DAW TO DO para organizar tus tareas.</p>
                    </div>

                    <!-- FORMULARIO DE RESEÑA -->
                    <div class="bg-white dark:bg-[#181820] rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-6 md:p-8 mb-12">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Escribir una valoración</h3>
                        <form @submit.prevent="submitReview" class="space-y-4">
                            
                            <div class="flex items-center gap-2 mb-4">
                                <button v-for="star in 5" :key="star" type="button" 
                                    @click="form.rating = star"
                                    @mouseenter="hoverRating = star"
                                    @mouseleave="hoverRating = 0"
                                    class="focus:outline-none transition-transform hover:scale-110">
                                    <svg class="w-8 h-8 transition-colors duration-200" 
                                        :class="(hoverRating ? star <= hoverRating : star <= form.rating) ? 'text-yellow-400 drop-shadow-sm' : 'text-gray-200 dark:text-gray-700'" 
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <span class="ml-3 text-sm font-medium text-gray-500">
                                    {{ form.rating === 5 ? '¡Excelente!' : form.rating === 4 ? 'Muy buena' : form.rating === 3 ? 'Normal' : form.rating === 2 ? 'Mejorable' : form.rating === 1 ? 'Mala' : 'Puntúa la app' }}
                                </span>
                            </div>

                            <textarea v-model="form.comment" rows="3" required placeholder="¿Qué te parece la aplicación? ¿Te está ayudando a organizar tus tareas?" 
                                class="w-full bg-gray-50 dark:bg-[#13131A] border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-gray-900 dark:text-white focus:ring-1 focus:ring-indigo-500 resize-none"></textarea>
                            
                            <div class="flex justify-end">
                                <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-lg shadow-indigo-600/20 disabled:opacity-50 transition-all">
                                    {{ form.processing ? 'Enviando...' : 'Publicar Reseña' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- MURO DE RESEÑAS -->
                    <div class="space-y-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-800 pb-4">Últimas Valoraciones ({{ reviews.length }})</h3>
                        
                        <div v-if="reviews.length === 0" class="text-center py-12 text-gray-500">
                            Aún no hay reseñas. ¡Sé el primero en valorar DAW TO DO!
                        </div>

                        <div v-for="review in reviews" :key="review.id" class="bg-white dark:bg-[#181820] p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800/60">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center font-bold text-gray-600 dark:text-gray-300 text-sm">
                                        {{ getInitials(review.user.name) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-gray-900 dark:text-white">{{ review.user.name }}</p>
                                        <p class="text-xs text-gray-500">{{ formatDate(review.created_at) }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-0.5">
                                    <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-200 dark:text-gray-700'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">{{ review.comment }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <div class="fixed bottom-6 right-6 z-[60] flex flex-col gap-3 pointer-events-none">
            <div v-for="notif in notifications" :key="notif.id" class="transform transition-all duration-300 flex items-center gap-3 px-5 py-3 rounded-xl shadow-2xl text-sm font-medium text-white min-w-[250px] bg-indigo-600">
                {{ notif.message }}
            </div>
        </div>

    </div>
</template>