<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kostum;
use App\Models\Sewa;
use App\Models\Keranjang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Bayar;
use App\Models\Transaksi;

class SewaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_sewa' => 'required|date|after_or_equal:today',
            'tanggal_kembali' => 'required|date|after:tanggal_sewa',
            'items' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            // Ambil item keranjang
            $keranjang_items = Keranjang::whereIn('id', $request->items)
                              ->where('user_id', auth()->id())
                              ->where('status', 'active')
                              ->with('kostum')
                              ->get();

            foreach ($keranjang_items as $item) {
                // Hitung total hari
                $tanggal_sewa = Carbon::parse($request->tanggal_sewa);
                $tanggal_kembali = Carbon::parse($request->tanggal_kembali);
                $total_hari = $tanggal_kembali->diffInDays($tanggal_sewa);

                // Hitung total harga
                $total_harga = $item->kostum->harga_sewa * $total_hari * $item->quantity;

                // Buat sewa baru
                $sewa = new Sewa();
                $sewa->user_id = auth()->id();
                $sewa->kostum_id = $item->kostum_id;
                $sewa->tanggal_sewa = $request->tanggal_sewa;
                $sewa->tanggal_kembali = $request->tanggal_kembali;
                $sewa->jumlah_sewa = $item->quantity;
                $sewa->total_harga = $total_harga;
                $sewa->status = 'pending';
                $sewa->save();

                // Update status keranjang
                $item->update(['status' => 'checked_out']);
            }

            DB::commit();

            return redirect()->route('user.sewa.index')
                            ->with('success', 'Checkout berhasil! Silahkan cek riwayat sewa Anda.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function adminIndex()
    {
        $title = 'Data Penyewaan';
        $sewa = Sewa::with(['kostum', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();
        return view('admin.sewa.index', compact('title', 'sewa'));
    }

    public function userIndex() 
    {
        $sewa = Sewa::where('user_id', auth()->user()->id)
                    ->with(['kostum'])
                    ->orderBy('created_at', 'desc')
                    ->get();
        return view('user.sewa.index', compact('sewa'));
    }

    public function show($id)
    {
        $sewa = Sewa::where('id_sewa', $id)->firstOrFail();
        // Cek apakah user yang login adalah pemilik sewa
        if ($sewa->user_id != auth()->user()->id) {
            abort(403);
        }
        
        return view('user.sewa.show', compact('sewa'));
    }

    public function adminShow($id)
    {
        $sewa = Sewa::where('id_sewa', $id)->firstOrFail();
        $title = 'Detail Sewa';
        return view('admin.sewa.show', compact('title', 'sewa'));
    }

    public function verifikasi($id)
    {
        try {
            DB::beginTransaction();
            
            $sewa = Sewa::where('id_sewa', $id)->firstOrFail();
            $sewa->status = 'approved';
        $sewa->save();

            DB::commit();
            
            return redirect()->route('admin.sewa.index')
                            ->with('status', 'success')
                            ->with('message', 'Penyewaan berhasil diverifikasi');
                            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Verifikasi error: ' . $e->getMessage());
            return back()
                    ->with('status', 'error')
                    ->with('message', 'Terjadi kesalahan saat verifikasi: ' . $e->getMessage());
        }
    }

    public function pembayaran($id)
    {
        $sewa = Sewa::with('kostum')->where('id_sewa', $id)->firstOrFail();
        if ($sewa->user_id != auth()->user()->id) {
            abort(403);
        }
        return view('user.sewa.pembayaran', compact('sewa'));
    }

    public function prosesBayar(Request $request, $id)
    {
        $request->validate([
            'no_rekening' => 'required|string',
            'bank_tujuan' => 'required|in:BCA,BNI,MANDIRI',
            'jumlah_bayar' => 'required|numeric'
        ]);

        try {
            DB::beginTransaction();

            $sewa = Sewa::where('id_sewa', $id)->firstOrFail();
            
            // Buat ID Transaksi
            $id_transaksi = 'TRX-' . date('YmdHis');

            // Simpan data pembayaran
            $bayar = new Bayar();
            $bayar->id_sewa = $sewa->id_sewa;
            $bayar->id_transaksi = $id_transaksi;
            $bayar->no_rekening = $request->no_rekening;
            $bayar->bank_tujuan = $request->bank_tujuan;
            $bayar->jumlah_bayar = $request->jumlah_bayar;
            $bayar->save();

            // Simpan data transaksi
            $transaksi = new Transaksi();
            $transaksi->id_sewa = $sewa->id_sewa;
            $transaksi->nama_kostum = $sewa->kostum->nama_kostum;
            $transaksi->jumlah_sewa = $sewa->jumlah_sewa;
            $transaksi->total_harga = $sewa->total_harga;
            $transaksi->tgl_transaksi = now();
            $transaksi->save();

            DB::commit();

            return redirect()->route('user.sewa.index')
                            ->with('status', 'success')
                            ->with('message', 'Pembayaran berhasil diproses');

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                    ->with('status', 'error')
                    ->with('message', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function verifikasiPembayaran($id)
    {
        try {
            DB::beginTransaction();

            $sewa = Sewa::where('id_sewa', $id)->firstOrFail();
            $sewa->status = 'approved';
            $sewa->save();

            DB::commit();

            return redirect()->route('admin.sewa.index')
                            ->with('status', 'success')
                            ->with('message', 'Pembayaran berhasil diverifikasi');

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                    ->with('status', 'error')
                    ->with('message', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail sewa untuk user
     */
    public function userShow($id)
    {
        $sewa = Sewa::with(['kostum', 'bayar'])
            ->where('user_id', auth()->id())
            ->where('id_sewa', $id)
            ->firstOrFail();

        return view('user.sewa.show', compact('sewa'));
    }
}