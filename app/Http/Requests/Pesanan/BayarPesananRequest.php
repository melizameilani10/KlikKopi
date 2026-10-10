<?php

namespace App\Http\Requests\Pesanan;

use App\Http\Requests\ApiFormRequest;

class BayarPesananRequest extends ApiFormRequest
{
    /**
     * Aturan validasi pembayaran.
     * Bukti opsional: hanya jpg/jpeg/png, maksimal 2048 KB.
     */
    public function rules(): array
    {
        return [
            'id_metode'    => ['required', 'integer', 'exists:metode_pembayaran,id_metode'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0'],
            'bukti'        => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_metode.required'    => 'Metode pembayaran wajib dipilih.',
            'id_metode.exists'      => 'Metode pembayaran tidak ditemukan.',
            'jumlah_bayar.required' => 'Jumlah bayar wajib diisi.',
            'jumlah_bayar.numeric'  => 'Jumlah bayar harus berupa angka.',
            'jumlah_bayar.min'      => 'Jumlah bayar tidak boleh negatif.',
            'bukti.file'            => 'Bukti pembayaran harus berupa berkas.',
            'bukti.mimes'           => 'Bukti pembayaran harus berformat jpg, jpeg, atau png.',
            'bukti.max'             => 'Ukuran bukti pembayaran maksimal 2048 KB.',
        ];
    }
}
