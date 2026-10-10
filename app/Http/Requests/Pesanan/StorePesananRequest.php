<?php

namespace App\Http\Requests\Pesanan;

use App\Http\Requests\ApiFormRequest;

class StorePesananRequest extends ApiFormRequest
{
    /**
     * Aturan validasi pembuatan pesanan.
     * Meja & produk wajib ada di database, jumlah minimal 1.
     */
    public function rules(): array
    {
        return [
            'id_meja'              => ['required', 'integer', 'exists:meja,id_meja'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.id_produk'    => ['required', 'integer', 'exists:produk,id_produk'],
            'items.*.jumlah'       => ['required', 'integer', 'min:1'],
            'items.*.topping'      => ['nullable', 'string', 'max:255'],
            'items.*.gula'         => ['nullable', 'string', 'max:255'],
            'items.*.es'           => ['nullable', 'string', 'max:255'],
            'items.*.catatan'      => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_meja.required'          => 'Meja wajib dipilih.',
            'id_meja.integer'           => 'Meja tidak valid.',
            'id_meja.exists'            => 'Meja tidak ditemukan.',
            'items.required'            => 'Pesanan harus memiliki minimal 1 produk.',
            'items.array'               => 'Daftar produk tidak valid.',
            'items.min'                 => 'Pesanan harus memiliki minimal 1 produk.',
            'items.*.id_produk.required' => 'Produk wajib dipilih pada setiap item.',
            'items.*.id_produk.exists'  => 'Produk tidak ditemukan.',
            'items.*.jumlah.required'   => 'Jumlah wajib diisi.',
            'items.*.jumlah.integer'    => 'Jumlah harus berupa angka bulat.',
            'items.*.jumlah.min'        => 'Jumlah minimal 1.',
        ];
    }
}
