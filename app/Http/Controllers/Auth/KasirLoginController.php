<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\KasirLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KasirLoginController extends Controller
{
    public function create(): View
    {
        return view('kasir.login');
    }

    public function store(KasirLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $shift = $request->validated('shift');
        $label = config("perkoci.kasir.shifts.{$shift}.label", ucfirst($shift));
        $request->session()->put('pos.shift', $shift);
        $request->session()->put('pos.shift_label', $label);

        return redirect()
            ->intended(route('kasir.dashboard'))
            ->with('success', "Shift {$label} dibuka. Selamat bertugas!");
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('kasir.login')
            ->with('success', 'Shift ditutup. Anda telah keluar dari terminal.');
    }
}
