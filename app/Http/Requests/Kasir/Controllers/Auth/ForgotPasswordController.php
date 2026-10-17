<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ForgotPasswordController extends Controller
{
    /**
     * Placeholder: the real reset flow comes in a later iteration.
     */
    public function __invoke(): RedirectResponse
    {
        return redirect()
            ->route('login')
            ->with('info', 'Fitur reset kata sandi akan tersedia pada tahap berikutnya. Silakan hubungi administrator sistem untuk bantuan.');
    }
}
