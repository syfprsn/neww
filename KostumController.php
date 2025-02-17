<?php

namespace App\Http\Controllers;

use App\Models\Kostum;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KostumController extends Controller
{
    /**
     * Tampilkan semua data kostum.
     */
    public function index()
    {
        $title = 'Kostum';
        $kostum = Kostum::latest()->get();

        return view('admin.kostum.index', compact('title', 'kostum'));
    }

    /**
     * Tampilkan form untuk menambah kostum baru.
     */
    public function create()
    {
        $title = 'Tambah Kostum';
        return view('admin.kostum.create', compact('title'));
    }

    /**
     * Simpan kostum baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kostum' => 'required|string|max:255',
            'harga_sewa' => 'required|numeric',
            'deskripsi' => 'nullable|string',
            'status_kostum' => 'required|in:available,rented,maintenance',
            'stok' => 'required|integer|min:1', // Validasi stok
            'gambar_kostum' => 'nullable|image|max:2048',
        ]);

        // Tambahkan validasi untuk mencegah duplikasi berdasarkan nama_kostum
        if (Kostum::where('nama_kostum', $validated['nama_kostum'])->exists()) {
            return redirect()->back()->withErrors(['nama_kostum' => 'Kostum dengan nama ini sudah ada.'])->withInput();
        }

        if ($request->hasFile('gambar_kostum')) {
            $file = $request->file('gambar_kostum');
            $filename = time() . '_' . $file->getClientOriginalName(); // Menggunakan timestamp untuk nama file unik
            $file->move(public_path('produk'), $filename); // Pindahkan file ke public/produk
            $validated['gambar_kostum'] = 'produk/' . $filename; // Simpan path relatif
        }
        // Tambahkan kode_kostum secara manual
        $validated['kode_kostum'] = Str::random(6);

        // Simpan ke dalam database
        $kostum = Kostum::create($validated);

        return $kostum
            ? redirect()->route('kostum.index')->with('status', 'success')->with('title', 'Berhasil')->with('message', 'Kostum Berhasil Ditambahkan')
            : redirect()->route('kostum.index')->with('status', 'danger')->with('title', 'Gagal')->with('message', 'Kostum Gagal Ditambahkan');
    }
    

    /**
     * Tampilkan form untuk mengedit kostum.
     */
    public function edit($id)
    {
        $title = 'Edit Kostum';
        $kostum = Kostum::findOrFail($id);

        return view('admin.kostum.edit', compact('title', 'kostum'));
    }

    /**
     * Perbarui data kostum di database.
     */
    public function update(Request $request, $id)
{
    $validated = $request->validate([
        'nama_kostum' => 'required|string|max:255',
        'kode_kostum' => 'required|string|max:255',
        'harga_sewa' => 'required|numeric',
        'deskripsi' => 'nullable|string',
        'status_kostum' => 'required|in:available,rented,maintenance',
        'stok' => 'required|integer|min:1',
        'gambar_kostum' => 'nullable|image|max:2048',
    ]);

    $kostum = Kostum::findOrFail($id);

    // Jika ada gambar baru, hapus gambar lama lalu simpan gambar baru
    if ($request->hasFile('gambar_kostum')) {
        // Hapus gambar lama jika ada
        if ($kostum->gambar_kostum && file_exists(public_path($kostum->gambar_kostum))) {
            unlink(public_path($kostum->gambar_kostum)); // Hapus file gambar lama
        }
        $file = $request->file('gambar_kostum');
        $filename = time() . '_' . $file->getClientOriginalName(); // Menggunakan timestamp untuk nama file unik
        $file->move(public_path('produk'), $filename); // Pindahkan file ke public/produk
        $validated['gambar_kostum'] = 'produk/' . $filename; // Simpan path relatif
    }

    // Update data kostum termasuk stok
    $updated = $kostum->update($validated);

    return $updated
        ? redirect()->route('kostum.index')->with('status', 'success')->with('title', 'Berhasil')->with('message', 'Kostum Berhasil Diupdate')
        : redirect()->route('kostum.index')->with('status', 'danger')->with('title', 'Gagal')->with('message', 'Kostum Gagal Diupdate');
}

    /**
     * Hapus kostum dari database.
     */
    public function destroy($id)
    {
        $kostum = Kostum::findOrFail($id);

        // Hapus gambar terkait jika ada
        if ($kostum->gambar_kostum && Storage::exists('public/' . $kostum->gambar_kostum)) {
            Storage::delete('public/' . $kostum->gambar_kostum);
        }

        $deleted = $kostum->delete();

        return $deleted
            ? redirect()->route('kostum.index')->with('status', 'success')->with('title', 'Berhasil')->with('message', 'Kostum Berhasil Dihapus')
            : redirect()->route('kostum.index')->with('status', 'danger')->with('title', 'Gagal')->with('message', 'Kostum Gagal Dihapus');
    }
}
