<?php

namespace App\Http\Requests\Kasir;

use App\Http\Requests\ApiFormRequest;

class VerifikasiPesananRequest extends ApiFormRequest
{
    /**
     * Status akhir yang diizinkan untuk verifikasi kasir
     * (mengikuti enum status_pesanan, tanpa 'pending').
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:diproses,selesai,batal'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status pesanan wajib diisi.',
            'status.in'       => 'Status hanya boleh diproses, selesai, atau batal.',
        ];
    }
}
