<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Support\CustomerData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function checkout(): View
    {
        $cart = CustomerData::cart();
        if (empty($cart)) {
            return view('order.cart', [
                'table' => session('order.table', CustomerData::resolveTable()),
                'cart' => [],
                'totals' => CustomerData::totals([]),
                'cartCount' => 0,
                'activeNav' => 'cart',
                'emptyCheckout' => true,
            ]);
        }

        return view('order.checkout', [
            'table' => session('order.table', CustomerData::resolveTable()),
            'cart' => $cart,
            'totals' => CustomerData::totals($cart),
            'methods' => CustomerData::paymentMethods(),
            'cartCount' => CustomerData::cartCount(),
            'activeNav' => 'cart',
        ]);
    }

    /**
     * Buat pesanan — integration-ready: snapshot tersimpan di session.
     * TODO: simpan ke tabel pesanan/detail_pesanan/pembayaran
     * saat backend order customer dihubungkan.
     */
    public function store(Request $request): RedirectResponse
    {
        $cart = CustomerData::cart();
        abort_if(empty($cart), 422, 'Keranjang masih kosong.');

        $data = $request->validate([
            'method' => ['required', 'in:qris,ewallet,debit,paylater'],
        ]);

        $totals = CustomerData::totals($cart);
        $table = session('order.table', CustomerData::resolveTable());
        $now = now();

        $order = [
            'code' => 'ORD-'.strtoupper($now->format('md').'-'.substr(uniqid(), -4)),
            'ref' => 'PERKOCI-'.$now->format('YmdHis'),
            'table' => $table,
            'items' => array_values($cart),
            'totals' => $totals,
            'method' => $data['method'],
            'method_label' => collect(CustomerData::paymentMethods())->firstWhere('key', $data['method'])['label'],
            'status' => 'Pesanan Diterima',
            'step' => 1,
            'paid' => $data['method'] !== 'paylater',
            'time' => $now->translatedFormat('d M Y • H:i').' WIB',
            'cashier' => 'Sistem Self-Order',
        ];

        session(['order.placed' => $order, 'order.cart' => []]);

        return redirect()->route('order.status')->with('success', "Pesanan {$order['code']} diterima!");
    }

    public function status(): View
    {
        return view('order.status', [
            'table' => session('order.table', CustomerData::resolveTable()),
            'order' => session('order.placed'),
            'cartCount' => CustomerData::cartCount(),
            'activeNav' => 'status',
        ]);
    }

    public function receipt(): View
    {
        $order = session('order.placed');
        abort_if(! $order, 404, 'Belum ada transaksi.');

        return view('order.receipt', [
            'table' => session('order.table', CustomerData::resolveTable()),
            'order' => $order,
            'cartCount' => CustomerData::cartCount(),
            'activeNav' => 'status',
        ]);
    }
}
