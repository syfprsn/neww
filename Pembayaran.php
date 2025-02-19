<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'Pembayaran';
    protected $fillable = [
        'id_transaksi',
        'waktu_pembayaran',
        'total',
        'metode',
        'nomor_tujuan',
    ];
}
