<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bayar;
use App\Models\Sewa;
use Illuminate\Support\Facades\DB;

class BayarController extends Controller
{
    /**
     * Tampilkan semua data pembayaran.
     */
    public function index()
    {
        $title = 'Data Pembayaran';
        $pembayaran = Bayar::with(['sewa' => function($query) {
            $query->with(['user', 'kostum']);
        }])
        ->orderBy('created_at', 'desc')
        ->get();
        
        return view('admin.pembayaran.index', compact('title', 'pembayaran'));
    }

    /**
     * Tampilkan form untuk membuat pembayaran baru.
     */
    public function create()
    {
        $title = 'Tambah Pembayaran';
        $sewa = Sewa::all(); // Ambil semua data sewa untuk dropdown relasi
        $bank = ['BCA', 'BNI', 'Mandiri']; // Opsi bank tujuan
        return view('admin.bayar.create', compact('title', 'sewa', 'bank'));
    }

    /**
     * Simpan data pembayaran baru ke database.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'sewa_id' => 'required|exists:sewa,id',
            'id_transaksi' => 'required|string|max:255',
            'no_rekening' => 'required|string|max:50',
            'bank_tujuan' => 'required|in:BCA,BNI,Mandiri',
            'jumlah_bayar' => 'required|numeric|min:1',
        ]);

        $bayar = Bayar::create($validated);

        return $bayar
            ? redirect()->route('bayar.index')->with('status', 'success')->with('message', 'Pembayaran berhasil ditambahkan.')
            : redirect()->route('bayar.index')->with('status', 'danger')->with('message', 'Pembayaran gagal ditambahkan.');
    }

    /**
     * Tampilkan form untuk mengedit pembayaran.
     */
    public function edit($id)
    {
        $title = 'Edit Pembayaran';
        $bayar = Bayar::findOrFail($id);
        $sewa = Sewa::all(); // Ambil data sewa untuk dropdown
        $banks = ['BCA', 'BNI', 'Mandiri']; // Opsi bank tujuan

        return view('admin.bayar.edit', compact('title', 'bayar', 'sewa', 'banks'));
    }

    /**
     * Perbarui data pembayaran di database.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'sewa_id' => 'required|exists:sewas,id',
            'transaksi_id' => "required|string|max:255|unique:bayars,transaksi_id,{$id}",
            'no_rekening' => 'required|string|max:50',
            'bank_tujuan' => 'required|in:BCA,BNI,Mandiri',
            'jumlah_bayar' => 'required|numeric|min:1',
        ]);

        $bayar = Bayar::findOrFail($id);
        $bayar->update($validated);

        return redirect()->route('bayar.index')->with('status', 'success')->with('message', 'Pembayaran berhasil diperbarui.');
    }

    /**
     * Hapus data pembayaran dari database.
     */
    public function destroy($id)
    {
        $bayar = Bayar::findOrFail($id);
        $bayar->delete();

        return redirect()->route('bayar.index')->with('status', 'success')->with('message', 'Pembayaran berhasil dihapus.');
    }

    public function verifikasi($id)
    {
        try {
            DB::beginTransaction();
            
            $pembayaran = Bayar::findOrFail($id);
            $sewa = $pembayaran->sewa;
            $kostum = $sewa->kostum;
            
            // Cek stok kostum
            if ($kostum->stok < $sewa->jumlah_sewa) {
                throw new \Exception('Stok kostum tidak mencukupi');
            }
            
            // Update status sewa menjadi approved
            $sewa->status = 'approved';
            $sewa->save();
            
            // Kurangi stok kostum
            $kostum->stok = $kostum->stok - $sewa->jumlah_sewa;
            $kostum->save();
            
            DB::commit();
            
            return redirect()->route('admin.pembayaran.index')
                            ->with('status', 'success')
                            ->with('message', 'Pembayaran berhasil diverifikasi dan stok kostum telah diperbarui');
                            
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                    ->with('status', 'error')
                    ->with('message', 'Terjadi kesalahan saat verifikasi: ' . $e->getMessage());
        }
    }

    public function tolak(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $pembayaran = Bayar::findOrFail($id);
            $sewa = $pembayaran->sewa;
            
            // Update status sewa menjadi rejected
            $sewa->status = 'rejected';
            $sewa->keterangan = $request->keterangan;
            $sewa->save();
            
            DB::commit();
            
            return redirect()->route('admin.pembayaran.index')
                            ->with('status', 'success')
                            ->with('message', 'Pembayaran berhasil ditolak');
                            
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                    ->with('status', 'error')
                    ->with('message', 'Terjadi kesalahan saat menolak pembayaran: ' . $e->getMessage());
        }
    }
}
