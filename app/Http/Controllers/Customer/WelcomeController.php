<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Support\CustomerData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function welcome(Request $request): View
    {
        $table = CustomerData::resolveTable(
            $request->query('meja'),
            $request->query('qr')
        );
        session(['order.table' => $table]);

        return view('order.welcome', [
            'table' => $table,
            'cartCount' => CustomerData::cartCount(),
        ]);
    }

    /**
     * Panggil waiter — dibatasi 1x per 60 detik per sesi
     * agar tidak spam jika tombol ditekan berkali-kali.
     */
    public function waiter(Request $request)
    {
        $last = (int) session('order.waiter_at', 0);
        $cooldown = 60 - (time() - $last);

        if ($cooldown > 0) {
            return response()->json([
                'success' => false,
                'message' => "Pelayan sudah dipanggil. Coba lagi dalam {$cooldown} detik.",
                'cooldown' => $cooldown,
            ], 429);
        }

        session(['order.waiter_at' => time()]);
        $table = session('order.table', CustomerData::resolveTable());

        return response()->json([
            'success' => true,
            'message' => "Pelayan akan segera menuju {$table['label']}.",
        ]);
    }
}
