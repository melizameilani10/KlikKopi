<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class HealthController extends Controller
{
    /**
     * Mengecek kesiapan server dan status konektivitas basis data MySQL
     */
    public function check()
    {
        try {
            // Melakukan ping query sederhana ke MySQL
            DB::connection()->getPdo();
            $dbStatus = "CONNECTED";
            $produkCount = DB::table('produk')->count();
            $mejaCount = DB::table('meja')->count();
        } catch (Exception $e) {
            $dbStatus = "DISCONNECTED: " . $e->getMessage();
            $produkCount = 0;
            $mejaCount = 0;
        }

        return response()->json([
            'system_name' => 'KlikKopi',
            'sprint_stage' => 'Sprint 1 - Foundation Ready',
            'database_status' => $dbStatus,
            'active_produk_seeded' => $produkCount,
            'active_meja_seeded' => $mejaCount,
            'timestamp' => now()->toIso8601String()
        ]);
    }
}
