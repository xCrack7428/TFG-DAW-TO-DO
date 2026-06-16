<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    // Redirige al usuario a Google/GitHub
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    // Recibe los datos cuando el usuario acepta
    public function callback($provider)
    {
        $socialUser = Socialite::driver($provider)->user();

        // 1. Sacamos el email (o nos inventamos uno si GitHub lo oculta)
        $email = $socialUser->getEmail() ?? $socialUser->getId() . '@' . $provider . '.com';

        // 2. Buscamos si ya existe un usuario con ese correo en la base de datos
        $user = User::where('email', $email)->first();

        if ($user) {
            // Si el correo ya existe (ej. entró antes con Google), 
            // simplemente le actualizamos el "provider" y le dejamos pasar.
            $user->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);
        } else {
            // Si el correo NO existe en la base de datos, lo creamos desde cero
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                'email' => $email,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'password' => null,
            ]);
        }

        // 3. Lo logueamos en el sistema
        Auth::login($user);

        // 4. Lo mandamos al panel de tareas
        return redirect('/dashboard');
    }
}
