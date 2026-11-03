<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'university_id' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ];
    }

    public function validateCredentials(): User
    {
        $this->ensureIsNotRateLimited();

        $user = User::query()
            ->where('university_id', (string) $this->string('university_id'))
            ->first();

        if ($user === null || ! Hash::check((string) $this->string('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'university_id' => 'الرقم الجامعي أو كلمة المرور غير صحيحة.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $user;
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'university_id' => "محاولات كثيرة. جرّب بعد {$seconds} ثانية.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower((string) $this->string('university_id')).'|'.$this->ip());
    }
}
