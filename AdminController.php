<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Transaksi;
use App\Models\Produk;

class AdminController extends Controller
{
    public function approvePayment($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->status = 'approved';
        $pembayaran->save();

        // Kurangi stok produk
        $transaksi = Transaksi::findOrFail($pembayaran->id_transaksi);
        $cartItems = $transaksi->cartItems;

        foreach ($cartItems as $item) {
            $produk = Produk::findOrFail($item->produk_id);
            $produk->stok -= $item->quantity;
            $produk->save();
        }

        return redirect()->route('admin.payments')->with('success', 'Payment approved and stock updated successfully!');
    }
    public function payments()
{
    $payments = Pembayaran::where('status', 'pending')->get();
    return view('admin.payments', compact('payments'));
}
}
