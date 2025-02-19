<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index()
    {
        $title = 'USER';
        $produk = Produk::latest()->get();
        return view('admin.tampilan.produk', compact('title', 'produk'));
    }

    public function create()
    {
        $title = 'TAMBAH PRODUK';
        return view('admin.create.produk', compact('title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'stok' => 'required|numeric',
            'foto_produk' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $filename = 'default.jpg';
        if ($request->hasFile('foto_produk')) {
            $file = $request->file('foto_produk');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/produk', $filename);
        }

        Produk::create([
            'nama_produk' => $request->nama_produk,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'foto_produk' => $filename,
        ]);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan');
    }

    public function edit($id)
    {
        $title = 'EDIT';
        $produk = Produk::findOrFail($id);
        return view('admin.edit.produk', compact('title', 'produk'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'stok' => 'required|numeric',
            'foto_produk' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $produk = Produk::findOrFail($id);

        if ($request->hasFile('foto_produk')) {
            if ($produk->foto_produk && $produk->foto_produk !== 'default.jpg') {
                Storage::delete('public/produk/' . $produk->foto_produk);
            }
            $file = $request->file('foto_produk');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/produk', $filename);
        } else {
            $filename = $produk->foto_produk;
        }

        $produk->update([
            'nama_produk' => $request->nama_produk,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'foto_produk' => $filename,
        ]);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui');
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);

        if ($produk->foto_produk && $produk->foto_produk !== 'default.jpg') {
            Storage::delete('public/produk/' . $produk->foto_produk);
        }

        $produk->delete();

        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus');
    }
}
