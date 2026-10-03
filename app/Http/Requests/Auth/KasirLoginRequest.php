<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KasirLoginRequest extends FormRequest
{
    private const MAX_ATTEMPTS = 5;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => Str::lower(trim((string) $this->input('username'))),
            'pin'      => preg_replace('/\D/', '', (string) $this->input('pin')),
        ]);
    }

    public function rules(): array
    {
        return [
            'shift'    => ['required', Rule::in(array_keys(config('perkoci.kasir.shifts')))],
            'username' => ['required', 'string', 'max:100'],
            'pin'      => ['required', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'shift.required'    => 'Pilih shift kerja terlebih dahulu.',
            'shift.in'          => 'Shift yang dipilih tidak valid.',
            'username.required' => 'ID Kasir wajib diisi.',
            'username.max'      => 'ID Kasir maksimal 100 karakter.',
            'pin.required'      => 'PIN wajib diisi.',
            'pin.digits'        => 'PIN harus terdiri dari 6 digit angka.',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('username', $this->input('username'))->first();

        if (! $this->credentialsAreValid($user)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'auth' => 'ID Kasir atau PIN tidak sesuai. Silakan coba lagi.',
            ]);
        }

        Auth::login($user);

        RateLimiter::clear($this->throttleKey());
    }

    private function credentialsAreValid(?User $user): bool
    {
        if (! $user || blank($user->pin)) {
            return false;
        }

        if (Schema::hasColumn('users', 'role') && strtolower((string) $user->role) !== 'kasir') {
            return false;
        }

        return Hash::check((string) $this->input('pin'), $user->pin);
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'lockout' => "Terlalu banyak percobaan. Terminal dikunci sementara, coba lagi dalam {$seconds} detik.",
        ]);
    }

    private function throttleKey(): string
    {
        return 'kasir|'.Str::transliterate($this->input('username').'|'.$this->ip());
    }
}
