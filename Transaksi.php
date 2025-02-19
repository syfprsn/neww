<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;
    protected $table='Transaksi';
    protected $fillable = [
        'tanggal_transaksi',
        'id_pelanggan',
        'id_ongkir',
        'total_bayar',
        'keterangan',
    ];

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'transaksi_id');
    }
}
