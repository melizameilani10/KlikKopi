<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPesanan extends Model
{
    use HasFactory;

    protected $table = 'detail_pesanan';
    protected $primaryKey = 'id_detail';

    protected $fillable = [
        'id_pesanan',
        'id_produk',
        'jumlah',
        'harga',
        'subtotal',
        'topping',
        'gula',
        'es',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah'    => 'integer',
            'harga'     => 'decimal:2',
            'subtotal'  => 'decimal:2',
        ];
    }

    /** Pesanan induk. */
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    /** Produk yang dipesan. */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}
