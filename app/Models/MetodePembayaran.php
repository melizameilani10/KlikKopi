<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodePembayaran extends Model
{
    use HasFactory;

    protected $table = 'metode_pembayaran';
    protected $primaryKey = 'id_metode';

    protected $fillable = [
        'nama_metode',
    ];

    /** Pembayaran yang memakai metode ini. */
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_metode', 'id_metode');
    }
}
