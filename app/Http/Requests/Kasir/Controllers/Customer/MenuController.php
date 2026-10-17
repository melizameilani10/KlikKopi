<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Support\CustomerData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function menu(Request $request): View
    {
        $table = session('order.table')
            ?? CustomerData::resolveTable($request->query('meja'), $request->query('qr'));
        session(['order.table' => $table]);

        $products = CustomerData::products();
        $activeCategory = (string) $request->query('kategori', 'Semua');
        $search = trim((string) $request->query('q', ''));

        $filtered = array_values(array_filter($products, function ($p) use ($activeCategory, $search) {
            if ($activeCategory !== 'Semua' && ($p['category'] ?? '') !== $activeCategory) {
                return false;
            }
            if ($search !== '' && ! str_contains(strtolower($p['name'].' '.($p['desc'] ?? '')), strtolower($search))) {
                return false;
            }

            return true;
        }));

        $totals = CustomerData::totals();

        return view('order.menu', [
            'table' => $table,
            'categories' => CustomerData::categories(),
            'activeCategory' => $activeCategory,
            'search' => $search,
            'products' => $filtered,
            'promo' => CustomerData::promo(),
            'totals' => $totals,
            'cartCount' => $totals['count'],
            'activeNav' => 'menu',
        ]);
    }

    public function product(string $id): View
    {
        $product = CustomerData::findProduct($id);
        abort_if(! $product, 404, 'Menu tidak ditemukan.');

        $table = session('order.table', CustomerData::resolveTable());

        return view('order.product', [
            'table' => $table,
            'product' => $product,
            'cartCount' => CustomerData::cartCount(),
            'activeNav' => 'menu',
        ]);
    }
}
