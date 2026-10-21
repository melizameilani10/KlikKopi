<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';

    protected $fillable = [
        'id_pesanan',
        'id_metode',
        'jumlah_bayar',
        'kembalian',
        'status_pembayaran',
        'bukti_pembayaran',
        'tanggal_pembayaran',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_bayar'       => 'decimal:2',
            'kembalian'          => 'decimal:2',
            'tanggal_pembayaran' => 'datetime',
        ];
    }

    /** Pesanan yang dibayar. */
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    /** Metode pembayaran yang dipakai. */
    public function metode()
    {
        return $this->belongsTo(MetodePembayaran::class, 'id_metode', 'id_metode');
    }
}
