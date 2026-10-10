<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $primaryKey = 'id_produk';

    protected $fillable = [
        'nama_produk',
        'kategori',
        'harga',
        'deskripsi',
        'status',
    ];

    /** Catatan stok produk (satu baris per produk). */
    public function stok()
    {
        return $this->hasOne(Stok::class, 'id_produk', 'id_produk');
    }

    /** Rincian pesanan yang memuat produk ini. */
    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_produk', 'id_produk');
    }
}
