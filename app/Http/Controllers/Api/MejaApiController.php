<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meja;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MejaApiController extends Controller
{
    /**
     * GET /api/v1/meja
     * Daftar meja beserta status ketersediaannya (publik).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Meja::query();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $meja = $query->orderBy('no_meja')->get();

        return ApiResponse::success($meja, 'Daftar meja.');
    }
}
