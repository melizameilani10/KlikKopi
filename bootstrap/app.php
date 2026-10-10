<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tamu yang membuka halaman terlindungi diarahkan ke login.
        $middleware->redirectGuestsTo(fn () => route('login'));
        // Pengguna yang sudah login diarahkan ke dashboard sesuai role.
        $middleware->redirectUsersTo(fn () => route('dashboard'));

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $api = fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($api) {
            if ($api($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'data'    => null,
                ], 401);
            }

            return null;
        });

        // Validasi gagal -> 422 dengan format seragam.
        $exceptions->render(function (ValidationException $e, Request $request) use ($api) {
            if ($api($request)) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data'    => ['errors' => $e->errors()],
                ], 422);
            }

            return null;
        });

        // Data tidak ditemukan -> 404.
        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) use ($api) {
            if ($api($request)) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Data tidak ditemukan.',
                    'data'    => null,
                ], 404);
            }

            return null;
        });

        // Akses ditolak (middleware role) -> 403.
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) use ($api) {
            if ($api($request)) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Anda tidak memiliki akses.',
                    'data'    => null,
                ], 403);
            }

            return null;
        });

        // Error HTTP lain (mis. abort(403, ...)) -> 403/4xx seragam.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) use ($api) {
            $status = $e->getStatusCode();

            if ($api($request) && $status !== 404) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Permintaan tidak dapat diproses.',
                    'data'    => null,
                ], $status);
            }

            return null;
        });

        // Fallback 500 (hanya saat debug dimatikan agar stack trace tetap terlihat di lokal).
        $exceptions->render(function (\Throwable $e, Request $request) use ($api) {
            if ($api($request) && ! config('app.debug')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan pada server.',
                    'data'    => null,
                ], 500);
            }

            return null;
        });
    })->create();
