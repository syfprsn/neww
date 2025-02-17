<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index()
    {
        $title = "Pelanggan";
        $pelanggan = User::where('level', 'User')->get();

        return view('admin.pelanggan.index', compact('title', 'pelanggan'));
    }

    public function create()
    {
        $title = "Tambah Pelanggan";
        $user = User::where('level', 'User')->latest()->get();

        return view('admin.pelanggan.create', compact('title', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
        ]);

        $pelanggan = new Pelanggan();
        $pelanggan->user_id = $request->user;
        $pelanggan->alamat = $request->alamat;
        $pelanggan->no_hp = $request->no_hp;
        $pelanggan->save();

        if ($pelanggan) {
            return redirect()->route('pelanggan.index')->with('status', 'success')->with('title', 'Berhasil')->with('message', 'Pelanggan Berhasil Ditambahkan');
        } else {
            return redirect()->route('pelanggan.index')->with('status', 'danger')->with('status', 'Gagal')->with('message', 'Pelanggan Gagal Ditambahkan');
        }
    }

    public function edit($id)
    {
        $title = "Edit Pelanggan";
        $user = User::where('level', 'Pelanggan')->latest()->get();
        $pelanggan = Pelanggan::find($id);

        return view('admin.pelanggan.edit', compact('title', 'pelanggan', 'user'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
        ]);

        $pelanggan = Pelanggan::find($id);
        $pelanggan->user_id = $request->user;
        $pelanggan->alamat = $request->alamat;
        $pelanggan->no_hp = $request->no_hp;
        $pelanggan->save();

        if ($pelanggan) {
            return redirect()->route('pelanggan.index')->with('status', 'success')->with('title', 'Berhasil')->with('message', 'Pelanggan Berhasil Diubah');
        } else {
            return redirect()->route('pelanggan.index')->with('status', 'danger')->with('status', 'Gagal')->with('message', 'Pelanggan Gagal Diubah');
        }
    }

    public function destroy($id)
    {
        $pelanggan = Pelanggan::find($id);
        if ($pelanggan) {
            $pelanggan->delete();
            return redirect()->route('pelanggan.index')->with('status', 'success')->with('title', 'Berhasil')->with('message', 'Pelanggan Berhasil Dihapus');
        } else {
            return redirect()->route('pelanggan.index')->with('status', 'danger')->with('status', 'Gagal')->with('message', 'Pelanggan Gagal Dihapus');
        }
    }
}
