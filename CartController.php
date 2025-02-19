<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Produk;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaksi;
use App\Models\Ongkir;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function addToCart(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);
        $quantity = $request->input('quantity', 1); // Ambil kuantitas dari form, default 1
        $cartItem = CartItem::where('user_id', Auth::id())->where('produk_id', $id)->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
        } else {
            $cartItem = new CartItem();
            $cartItem->user_id = Auth::id();
            $cartItem->produk_id = $id;
            $cartItem->quantity = $quantity;
        }

        $cartItem->save();

        return redirect()->route('cart.view')->with('success', 'Product added to cart!');
    }

    public function viewCart()
    {
        $cartItems = CartItem::where('user_id', Auth::id())->get();
        return view('user.cart', compact('cartItems'));
    }

    public function updateCart(Request $request, $id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return redirect()->route('cart.view')->with('success', 'Cart updated successfully!');
    }

    public function removeFromCart($id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cartItem->delete();

        return redirect()->route('cart.view')->with('success', 'Product removed from cart!');
    }

    public function checkoutView()
    {
        $ongkirs = Ongkir::all();
        return view('user.checkout', compact('ongkirs'));
    }

    public function processCheckout(Request $request)
    {
        $cartItems = CartItem::where('user_id', Auth::id())->get();
        $totalBayar = 0;

        foreach ($cartItems as $item) {
            $totalBayar += $item->quantity * $item->produk->harga;
        }

        $transaksi = new Transaksi();
        $transaksi->tanggal_transaksi = now();
        $transaksi->id_pelanggan = Auth::id();
        $transaksi->id_ongkir = $request->ongkir;
        $transaksi->total_bayar = $totalBayar;
        $transaksi->keterangan = 'diproses';
        $transaksi->save();

        // Clear cart
        CartItem::where('user_id', Auth::id())->delete();

        return redirect()->route('payment.view', ['id' => $transaksi->id]);
    }

    public function paymentView($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        return view('user.payment', compact('transaksi'));
    }

    public function processPayment(Request $request, $id)
    {
        $request->validate([
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $transaksi = Transaksi::findOrFail($id);

        // Create Pembayaran record
        $pembayaran = new Pembayaran();
        $pembayaran->id_transaksi = $transaksi->id;
        $pembayaran->waktu_pembayaran = now();
        $pembayaran->total = $transaksi->total_bayar;
        $pembayaran->metode = $request->metode;
        $pembayaran->nomor_tujuan = $request->nomor_tujuan;

        if ($request->hasFile('bukti_pembayaran')) {
            $file = $request->file('bukti_pembayaran');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);

            $pembayaran->bukti_pembayaran = $filename;
        }

        $pembayaran->save();

        return redirect()->route('receipt.view', ['id' => $pembayaran->id])->with('success', 'Payment uploaded successfully!');
    }

    public function receiptView($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        return view('user.receipt', compact('pembayaran'));
    }
}

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
}
