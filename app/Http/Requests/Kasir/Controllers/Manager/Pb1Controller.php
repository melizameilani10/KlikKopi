<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Support\ManagerData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Pb1Controller extends Controller
{
    public function index(Request $request): View
    {
        [$dari, $sampai] = ManagerData::period($request->query('dari'), $request->query('sampai'));

        return view('manager.pb1.index', [
            'active' => 'pb1',
            'manager' => ManagerData::managerMeta($request->user()),
            'pb1' => ManagerData::pb1($dari, $sampai),
            'filters' => ['dari' => $dari, 'sampai' => $sampai],
        ]);
    }
}
