<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * API core authentication (Sanctum token).
 *
 * POST /api/login  — login via email ATAU username + password.
 * POST /api/logout — revoke token berjalan (auth:sanctum).
 *
 * Aturan role sama dengan login web: hanya role yang terdaftar
 * di config perkoci.roles (admin, kasir, manager).
 */
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'ID pengguna atau email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $login = Str::lower(trim((string) $request->input('login')));
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $login)->first();

        if (! $user || ! Hash::check((string) $request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'ID pengguna/email atau kata sandi tidak sesuai.',
            ], 401);
        }

        $role = strtolower((string) $user->role);
        if (! array_key_exists($role, config('perkoci.roles'))) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini belum memiliki peran yang valid. Hubungi administrator.',
            ], 403);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $role,
                ],
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil keluar. Token dicabut.',
        ]);
    }
}
