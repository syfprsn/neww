<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Sewa;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    // Menampilkan daftar transaksi
    public function index()
    {
        $title = 'Transaksi';

        $transaksi = Transaksi::with('sewa.user', 'sewa.kostum')->latest()->get();

        return view('admin.transaksi.index', compact('title', 'transaksi'));
    }

    // Menampilkan form tambah transaksi
    public function create()
    {
        $title = 'Tambah Transaksi';
        $sewa = Sewa::whereDoesntHave('transaksi')->get();

        return view('admin.transaksi.create', compact('title', 'sewa'));
    }

    // Menyimpan transaksi baru
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'id_sewa' => 'required|exists:sewa,id',
            'total_harga' => 'required|integer',
            'tgl_transaksi' => 'required|date',
            'nama_kostum' => 'required|string|max:255',
            'jumlah_sewa' => 'required|integer',
        ]);

        $transaksi = new Transaksi();
        $transaksi->id_sewa = $request->id_sewa;
        $transaksi->nama_kostum = $request->nama_kostum;
        $transaksi->jumlah_sewa = $request->jumlah_sewa;
        $transaksi->total_harga = $request->total_harga;
        $transaksi->tgl_transaksi = $request->tgl_transaksi;
        $transaksi->jumlah_sewa = $request->jumlah_sewa;

        $transaksi->save();

        if ($transaksi) {
            return redirect()->route('transaksi.index')->with('status', 'success')->with('title', 'Berhasil')->with('message', 'Transaksi Berhasil Ditambahkan');
        } else {
            return redirect()->route('transaksi.index')->with('status', 'danger')->with('title', 'Gagal')->with('message', 'Transaksi Gagal Ditambahkan');
        }
    }

    // Menampilkan form edit transaksi
    public function edit($id)
    {
        $title = 'Edit Transaksi';
        $transaksi = Transaksi::findOrFail($id);
        $sewa = Sewa::latest()->get();

        return view('admin.transaksi.edit', compact('title', 'transaksi', 'sewa'));
    }

    // Memperbarui data transaksi
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_sewa' => 'required|exists:sewa,id',
            'total_harga' => 'required|numeric|min:0',
            'tgl_transaksi' => 'required|date',
            'nama_kostum' => 'required|string|max:255',
            'jumlah_sewa' => 'required|integer|min:1',
        ]);

        $transaksi = Transaksi::findOrFail($id);
        $transaksi->id_sewa = $request->id_sewa;
        $transaksi->nama_kostum = $request->nama_kostum;
        $transaksi->jumlah_sewa = $request->jumlah_sewa;
        $transaksi->total_harga = $request->total_harga;
        $transaksi->tgl_transaksi = $request->tgl_transaksi;

        $transaksi->save();

        if ($transaksi) {
            return redirect()->route('transaksi.index')->with('status', 'success')->with('title', 'Berhasil')->with('message', 'Transaksi Berhasil Diperbarui');
        } else {
            return redirect()->route('transaksi.index')->with('status', 'danger')->with('title', 'Gagal')->with('message', 'Transaksi Gagal Diperbarui');
        }
    }

    // Menghapus data transaksi
    public function destroy($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->delete();

        if ($transaksi) {
            return redirect()->route('transaksi.index')->with('status', 'success')->with('title', 'Berhasil')->with('message', 'Transaksi Berhasil Dihapus');
        } else {
            return redirect()->route('transaksi.index')->with('status', 'danger')->with('title', 'Gagal')->with('message', 'Transaksi Gagal Dihapus');
        }
    }

    public function adminIndex()
    {
        $title = 'Data Transaksi';
        $transaksi = Transaksi::with(['sewa' => function($query) {
            $query->with(['user', 'kostum', 'bayar']);
        }])
        ->orderBy('created_at', 'desc')
        ->get();
        
        return view('admin.transaksi.index', compact('title', 'transaksi'));
    }
}
