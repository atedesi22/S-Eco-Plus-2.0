<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginValue = $this->input('email');
        $fieldType = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        // 1. Récupérer l'utilisateur qui tente de se connecter
        $user = \App\Models\User::where($fieldType, $loginValue)->first();

        if (! $user) {
            $this->handleFailedAttempt();
        }

        // 2. Application de vos Règles Métier Strictes
        if ($user->hasRole('SuperAdmin')) {
            // Le SuperAdmin a le droit de se connecter avec n'importe quel identifiant (email ou phone)
            $credentials = [$fieldType => $loginValue, 'password' => $this->input('password')];
        }
        elseif ($user->hasRole('Client')) {
            // Un client DOIT impérativement utiliser son numéro de téléphone
            if ($fieldType !== 'phone') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => __('Les clients doivent s\'authentifier uniquement avec leur numéro de téléphone.'),
                ]);
            }
            $credentials = ['phone' => $loginValue, 'password' => $this->input('password')];
        }
        else {
            // Tout le reste = Personnel interne. Ils DOIVENT utiliser leur email d'entreprise
            if ($fieldType !== 'email') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => __('Le personnel doit s\'authentifier uniquement avec leur adresse email d\'entreprise.'),
                ]);
            }
            $credentials = ['email' => $loginValue, 'password' => $this->input('password')];
        }

        // 3. Vérification du statut (Un utilisateur suspendu ne passe pas)
        if ($user->status !== 'active') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => __('Votre compte est actuellement suspendu. Veuillez contacter l\'administration.'),
            ]);
        }

        // 4. Tentative de connexion finale
        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            $this->handleFailedAttempt();
        }

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
        // Au lieu du message générique, on vérifie si l'utilisateur existe avec ce mot de passe
        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => "Identifiants trouvés pour le rôle [" . $user->roles->first()?->name . "], mais le mot de passe est incorrect.",
        ]);
    }
    throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => "Échec critique : l'authentification a rejeté ces identifiants pour le champ [{$fieldType}].",
        ]);

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Centralisation de l'erreur de mauvaise combinaison mot de passe / identifiant
     */
    protected function handleFailedAttempt(): void
    {
        RateLimiter::hit($this->throttleKey());

        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
