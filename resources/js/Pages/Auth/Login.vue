<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Iniciar Sesión" />

    <div class="min-h-screen bg-[#13131A] flex flex-col justify-center items-center font-sans px-4">
        
        <div class="w-full max-w-md bg-[#181820] border border-gray-800 rounded-xl shadow-2xl p-8">
            <!-- LOGO -->
            <div class="flex items-center justify-center gap-2 mb-2">
                <span class="text-indigo-500 font-bold text-2xl">✓</span>
                <span class="text-white font-bold text-lg tracking-wide">DAW TO DO</span>
            </div>

            <!-- TÍTULOS -->
            <h2 class="text-2xl font-bold text-white text-center mt-4">Bienvenido de nuevo</h2>
            <p class="text-sm text-gray-400 text-center mt-2 mb-8">Inicia sesión para gestionar tus tareas.</p>

            <div v-if="status" class="mb-4 font-medium text-sm text-emerald-500 bg-emerald-500/10 border border-emerald-500/20 p-3 rounded-md">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <!-- EMAIL -->
                <div class="mb-4">
                    <label for="email" class="block text-xs font-bold text-gray-300 mb-1">Email</label>
                    <input id="email" type="email" v-model="form.email" required autofocus autocomplete="username"
                        class="w-full bg-[#13131A] border border-gray-700 rounded-md px-4 py-2.5 text-white focus:outline-none focus:border-indigo-500 transition-colors"
                        placeholder="correo@ejemplo.com">
                    <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                </div>

                <!-- CONTRASEÑA -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-1">
                        <label for="password" class="block text-xs font-bold text-gray-300">Contraseña</label>
                        <Link v-if="route().has('password.request')" :href="route('password.request')" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </div>
                    <input id="password" type="password" v-model="form.password" required autocomplete="current-password"
                        class="w-full bg-[#13131A] border border-gray-700 rounded-md px-4 py-2.5 text-white focus:outline-none focus:border-indigo-500 transition-colors"
                        placeholder="••••••••">
                    <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                </div>

                <!-- BOTÓN PRINCIPAL -->
                <button type="submit" :disabled="form.processing"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-md transition-colors flex justify-center items-center disabled:opacity-50 shadow-lg shadow-indigo-600/20">
                    Iniciar Sesión
                </button>

                <!-- ENLACE A REGISTRO -->
                <div class="text-center text-sm text-gray-400 mt-8 pt-6 border-t border-gray-800">
                    ¿No tienes una cuenta? 
                    <Link :href="route('register')" class="text-indigo-400 hover:text-indigo-300 font-medium transition-colors">
                        Regístrate
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>