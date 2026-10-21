<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan';
    protected $primaryKey = 'id_pesanan';

    protected $fillable = [
        'kode_pesanan',
        'id_meja',
        'id_user',
        'nomor_antrean',
        'tanggal_pesan',
        'total_harga',
        'status_pesanan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pesan' => 'datetime',
            'total_harga'   => 'decimal:2',
        ];
    }

    /** Meja tempat pesanan dibuat. */
    public function meja()
    {
        return $this->belongsTo(Meja::class, 'id_meja', 'id_meja');
    }

    /** Akun customer yang membuat pesanan. */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /** Rincian item pesanan. */
    public function detail()
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan', 'id_pesanan');
    }

    /** Alias detail agar lebih eksplisit. */
    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan', 'id_pesanan');
    }

    /** Pembayaran yang tercatat untuk pesanan ini. */
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_pesanan', 'id_pesanan');
    }
}
