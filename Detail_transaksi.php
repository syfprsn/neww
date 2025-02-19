<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail_transaksi extends Model
{
    use HasFactory;
    protected $table = "Detail_transaksi";
    protected $fillable = [
        "id_transaksi",
        "id_produk",
        "harga_produk",
        "jumlah_produk",
        "subtotal",

    ];
}
