<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Registro" />

    <div class="min-h-screen bg-[#13131A] flex flex-col justify-center items-center font-sans px-4 py-8">
        
        <div class="w-full max-w-md bg-[#181820] border border-gray-800 rounded-xl shadow-2xl p-8">
            <!-- LOGO -->
            <div class="flex items-center justify-center gap-2 mb-2">
                <span class="text-indigo-500 font-bold text-2xl">✓</span>
                <span class="text-white font-bold text-lg tracking-wide">DAW TO DO</span>
            </div>

            <!-- TÍTULOS -->
            <h2 class="text-2xl font-bold text-white text-center mt-4">Crea tu cuenta</h2>
            <p class="text-sm text-gray-400 text-center mt-2 mb-8">Únete a DAW TO DO y empieza a organizar tus tareas.</p>

            <form @submit.prevent="submit">
                <!-- NOMBRE COMPLETO -->
                <div class="mb-4">
                    <label for="name" class="block text-xs font-bold text-gray-300 mb-1 uppercase">Nombre completo</label>
                    <input id="name" type="text" v-model="form.name" required autofocus autocomplete="name"
                        class="w-full bg-[#13131A] border border-gray-700 rounded-md px-4 py-2.5 text-white focus:outline-none focus:border-indigo-500 transition-colors"
                        placeholder="John Doe">
                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                </div>

                <!-- EMAIL -->
                <div class="mb-4">
                    <label for="email" class="block text-xs font-bold text-gray-300 mb-1 uppercase">Email</label>
                    <input id="email" type="email" v-model="form.email" required autocomplete="username"
                        class="w-full bg-[#13131A] border border-gray-700 rounded-md px-4 py-2.5 text-white focus:outline-none focus:border-indigo-500 transition-colors"
                        placeholder="tu@email.com">
                    <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                </div>

                <!-- CONTRASEÑA -->
                <div class="mb-4">
                    <label for="password" class="block text-xs font-bold text-gray-300 mb-1 uppercase">Contraseña</label>
                    <input id="password" type="password" v-model="form.password" required autocomplete="new-password"
                        class="w-full bg-[#13131A] border border-gray-700 rounded-md px-4 py-2.5 text-white focus:outline-none focus:border-indigo-500 transition-colors"
                        placeholder="••••••••">
                    <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                </div>

                <!-- CONFIRMAR CONTRASEÑA -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-xs font-bold text-gray-300 mb-1 uppercase">Confirmar contraseña</label>
                    <input id="password_confirmation" type="password" v-model="form.password_confirmation" required autocomplete="new-password"
                        class="w-full bg-[#13131A] border border-gray-700 rounded-md px-4 py-2.5 text-white focus:outline-none focus:border-indigo-500 transition-colors"
                        placeholder="••••••••">
                    <div v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</div>
                </div>

                <!-- BOTÓN PRINCIPAL -->
                <button type="submit" :disabled="form.processing"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-md transition-colors flex justify-center items-center disabled:opacity-50 uppercase text-sm tracking-wide">
                    Registrarse
                </button>

                <!-- SEPARADOR -->
                <div class="relative flex items-center py-6">
                    <div class="flex-grow border-t border-gray-700"></div>
                    <span class="flex-shrink-0 mx-4 text-gray-500 text-xs">O continuar con</span>
                    <div class="flex-grow border-t border-gray-700"></div>
                </div>

                <!-- BOTONES SOCIALES (Estéticos por ahora) -->
                <div class="flex gap-4 mb-6">
                    <button type="button" class="flex-1 bg-[#13131A] border border-gray-700 rounded-md py-2 flex items-center justify-center gap-2 hover:bg-gray-800 transition-colors cursor-not-allowed opacity-70" title="Próximamente">
                        <span class="text-sm font-medium text-gray-300">Google</span>
                    </button>
                    <button type="button" class="flex-1 bg-[#13131A] border border-gray-700 rounded-md py-2 flex items-center justify-center gap-2 hover:bg-gray-800 transition-colors cursor-not-allowed opacity-70" title="Próximamente">
                        <span class="text-sm font-medium text-gray-300">GitHub</span>
                    </button>
                </div>

                <!-- ENLACE A LOGIN -->
                <div class="text-center text-sm text-gray-400">
                    ¿Ya tienes una cuenta? 
                    <Link :href="route('login')" class="text-indigo-400 hover:text-indigo-300 font-medium transition-colors">
                        Iniciar Sesión
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>