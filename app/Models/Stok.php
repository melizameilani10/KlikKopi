<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
    use HasFactory;

    protected $table = 'stok';
    protected $primaryKey = 'id_stok';

    // Tabel stok hanya memiliki kolom updated_at (tanpa created_at).
    public $timestamps = false;

    protected $fillable = [
        'id_produk',
        'jumlah_stok',
        'min_stok',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_stok' => 'integer',
            'min_stok'    => 'integer',
            'updated_at'  => 'datetime',
        ];
    }

    /** Produk pemilik catatan stok ini. */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}
