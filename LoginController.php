<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function index(){
        $title = "LOGIN";
        return view("auth.login",compact("title"));
    }

    public function register(){
        $title = 'REGISTER';
        $level = ['pemilik', 'admin', 'pelanggan'];
        return view('auth.register', compact('title', 'level'));
    }

    public function authenticate(Request $request){
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if(Auth::attempt($credentials)){
        $request->session()->regenerate();
        if(Auth::user()->level == 'admin'){
            return redirect()->intended('/dashboard');
    }else{
        return redirect()->intended('/toko');
    }
}
return redirect()->back()->with('loginError', 'Username atau password salah');
}
    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function indexcreate(){
        $title = 'USER';
        $user = User::latest()->get();
        return view('admin.tampilan.pelanggan', compact('title', 'user'));
    }

    public function create()
        {
            $title = 'TAMBAH USER';
            $level = ['pemilik', 'admin', 'pelanggan'];
            return view('admin.create.pelanggan', compact('title', 'level'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'username' => 'required',
            'password' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'level' => 'required',
        ]);

        $user = new User();
        $user->nama = $request->nama;
        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->alamat = $request->alamat;
        $user->no_hp = $request->no_hp;
        $user->level = $request->level;
        $user->save();

        if($user){
            return redirect()->route('pelanggan.indexcreate')->with('status', 'success')->with('title', 'Berhasil')->with('message', 'Pengguna Berhasil Ditambahkan');
        } else{
            return redirect()->route('pelanggan.indexcreate')->with('status', 'danger')->with('title', 'Gagal')->with('message', 'Pengguna Gagal Ditambahkan');
        }
    }

    public function edit($id)
    {
        $title = 'EDIT';
        $user = User::find($id);
        $level = ['pemilik', 'admin', 'Pelanggan'];
        return view('admin.edit.pelanggan', compact('title', 'user', 'level'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required',
            'username' => 'required',
            'password' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'level' => 'required',
        ]);

        $user =  User::find($id);
        $user->nama = $request->nama;
        $user->username = $request->username;
        if($request->password){
            $user->password = Hash::make($request->password);
        }
        $user->alamat = $request->alamat;
        $user->no_hp = $request->no_hp;
        $user->level = $request->level;
        $user->save();

        if($user){
            return redirect()->route('pelanggan.indexcreate')->with('status', 'success')->with('title', 'Berhasil')->with('message', 'Pengguna Berhasil Diubah');
        } else{
            return redirect()->route('pelanggan.indexcreate')->with('status', 'danger')->with('title', 'Gagal')->with('message', 'Pengguna Gagal Diubah');
        }
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if($user){
            $user->delete();
            if($user){
                return redirect()->route('pelanggan.indexcreate')->with('status', 'success')->with('title', 'Berhasil')->with('message', 'Pengguna Berhasil Dihapus');
            } else{
                return redirect()->route('pelanggan.indexcreate')->with('status', 'danger')->with('title', 'Gagal')->with('message', 'Pengguna Gagal Dihapus');
            }
        }
    }
}
