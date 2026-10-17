<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Support\CustomerData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function cart(): View
    {
        $table = session('order.table', CustomerData::resolveTable());
        $cart = CustomerData::cart();
        $totals = CustomerData::totals($cart);

        return view('order.cart', [
            'table' => $table,
            'cart' => $cart,
            'totals' => $totals,
            'cartCount' => $totals['count'],
            'activeNav' => 'cart',
        ]);
    }

    /** Tambah item (fetch JSON dari halaman produk). */
    public function add(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'string'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:20'],
            'selections' => ['nullable', 'array'],
            'note' => ['nullable', 'string', 'max:120'],
        ]);

        $product = CustomerData::findProduct($data['id']);
        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Menu tidak ditemukan.'], 404);
        }
        if ($product['sold_out']) {
            return response()->json(['success' => false, 'message' => 'Maaf, menu ini sedang habis.'], 422);
        }

        $qty = (int) ($data['qty'] ?? 1);
        $note = trim((string) ($data['note'] ?? ''));

        // Validasi opsi wajib (radio required) & petakan delta harga.
        $options = [];
        foreach ($product['options'] ?? [] as $group) {
            $sel = ($data['selections'] ?? [])[$group['key']] ?? null;
            if ($group['type'] === 'radio') {
                if (($group['required'] ?? false) && ! $sel) {
                    return response()->json([
                        'success' => false,
                        'message' => "Pilih {$group['title']} terlebih dahulu.",
                    ], 422);
                }
                if ($sel) {
                    $found = collect($group['options'])->firstWhere('value', $sel);
                    if (! $found) {
                        return response()->json(['success' => false, 'message' => 'Opsi tidak valid.'], 422);
                    }
                    $options[] = ['group' => $group['title'], 'value' => $found['value'], 'delta' => $found['delta']];
                }
            } else {
                foreach ((array) $sel as $v) {
                    $found = collect($group['options'])->firstWhere('value', $v);
                    if ($found) {
                        $options[] = ['group' => $group['title'], 'value' => $found['value'], 'delta' => $found['delta']];
                    }
                }
            }
        }

        $key = md5($product['id'].json_encode($options).$note);
        $cart = CustomerData::cart();

        if (isset($cart[$key])) {
            $cart[$key]['qty'] = min(20, $cart[$key]['qty'] + $qty);
        } else {
            $cart[$key] = [
                'key' => $key,
                'product_id' => $product['id'],
                'name' => $product['name'],
                'base_price' => $product['price'],
                'qty' => $qty,
                'options' => $options,
                'note' => $note ?: null,
            ];
        }

        session(['order.cart' => $cart]);
        $totals = CustomerData::totals($cart);

        return response()->json([
            'success' => true,
            'message' => "{$product['name']} masuk keranjang.",
            'cartCount' => $totals['count'],
            'cartTotal' => $totals['total'],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string'],
            'qty' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $cart = CustomerData::cart();
        if (! isset($cart[$data['key']])) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan.'], 404);
        }

        if ((int) $data['qty'] === 0) {
            unset($cart[$data['key']]);
        } else {
            $cart[$data['key']]['qty'] = (int) $data['qty'];
        }

        session(['order.cart' => $cart]);
        $totals = CustomerData::totals($cart);

        return response()->json([
            'success' => true,
            'cartCount' => $totals['count'],
            'totals' => $totals,
            'lineTotal' => isset($cart[$data['key']]) ? CustomerData::lineTotal($cart[$data['key']]) : 0,
        ]);
    }

    public function remove(Request $request)
    {
        $data = $request->validate(['key' => ['required', 'string']]);
        $cart = CustomerData::cart();
        unset($cart[$data['key']]);
        session(['order.cart' => $cart]);
        $totals = CustomerData::totals($cart);

        return response()->json([
            'success' => true,
            'cartCount' => $totals['count'],
            'totals' => $totals,
        ]);
    }
}
