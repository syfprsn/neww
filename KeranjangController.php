<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use App\Models\User;
use App\Models\Kostum;
use App\Models\Sewa;
use Illuminate\Http\Request;

class KeranjangController extends Controller
{
    // Menampilkan keranjang pengguna
    public function index()
    {
        $title = 'Keranjang';
        $kostum = Kostum::all();

        // Ambil keranjang pengguna dengan status active
        $keranjang = Keranjang::with('kostum')
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->get();

        return view('keranjang.index', compact('title', 'keranjang', 'kostum'));
    }

    // Menambahkan item ke keranjang
    public function store(Request $request)
    {
        $request->validate([
            'kostum_id' => 'required|exists:kostum,id',  // Validasi kostum_id agar sesuai dengan tabel kostum
            'quantity' => 'required|integer|min:1',  // Validasi quantity untuk memastikan angka positif
        ]);

        $kostum = Kostum::findOrFail($request->kostum_id);  // Temukan kostum sesuai id
        $subtotal = $kostum->harga_sewa * $request->quantity;  // Hitung subtotal

        // Periksa apakah item sudah ada di keranjang
        $keranjang = Keranjang::where('user_id', auth()->id())
            ->where('kostum_id', $request->kostum_id)
            ->where('status', 'active')
            ->first();

        if ($keranjang) {
            // Jika sudah ada, tambahkan jumlah dan subtotal
            $keranjang->quantity += $request->quantity;
            $keranjang->subtotal += $subtotal;
        } else {
            // Jika belum ada, buat item baru di keranjang
            $keranjang = new Keranjang();
            $keranjang->user_id = auth()->id();
            $keranjang->kostum_id = $request->kostum_id;
            $keranjang->quantity = $request->quantity;
            $keranjang->subtotal = $subtotal;
            $keranjang->status = 'active';
        }

        $keranjang->save();  // Simpan ke database

        // Redirect ke halaman keranjang dengan pesan sukses
        return redirect()->route('keranjang.index')
            ->with('status', 'success')
            ->with('message', 'Item berhasil ditambahkan ke keranjang.');
    }

    // Menghapus item dari keranjang
    public function destroy($id)
    {
        $keranjang = Keranjang::where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->firstOrFail();  // Temukan item yang sesuai, jika tidak ada akan muncul error 404

        $keranjang->delete();  // Hapus item dari keranjang

        // Redirect setelah item dihapus dengan pesan sukses
        return redirect()->route('keranjang.index')
            ->with('status', 'success')
            ->with('message', 'Item berhasil dihapus dari keranjang.');
    }

    // Checkout keranjang
    public function checkout(Request $request)
    {
        $keranjang = Keranjang::where('user_id', auth()->id())
            ->where('status', 'active')
            ->get();

        // Pastikan keranjang tidak kosong
        if ($keranjang->isEmpty()) {
            return redirect()->route('keranjang.index')
                ->with('status', 'danger')
                ->with('message', 'Keranjang Anda kosong.');
        }

        // Hitung total harga
        $total_harga = $keranjang->sum('subtotal');

        // Simpan data transaksi ke tabel `sewa`
        foreach ($keranjang as $item) {
            Sewa::create([
                'user_id' => $item->user_id,
                'kostum_id' => $item->kostum_id,
                'tanggal_sewa' => now(),  // Tanggal sewa saat ini
                'tanggal_kembali' => now()->addDays(3),  // Tanggal kembali setelah 3 hari
                'jumlah_sewa' => $item->quantity,
                'total_harga' => $item->subtotal,
            ]);
        }

        // Ubah status keranjang menjadi 'checked_out' setelah checkout
        Keranjang::where('user_id', auth()->id())
            ->where('status', 'active')
            ->update(['status' => 'checked_out']);

        // Redirect setelah checkout dengan pesan sukses
        return redirect()->route('keranjang.index')
            ->with('status', 'success')
            ->with('message', 'Checkout berhasil. Terima kasih!');
    }
}
