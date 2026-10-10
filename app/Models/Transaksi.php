<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_pembayaran',
        'id_user',
        'tanggal_transaksi',
        'status_transaksi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_transaksi' => 'datetime',
        ];
    }

    /** Pembayaran sumber transaksi ini. */
    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'id_pembayaran', 'id_pembayaran');
    }

    /** Kasir yang memproses transaksi. */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
