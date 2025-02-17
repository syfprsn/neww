<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sewa;
use App\Models\User;
use App\Models\Kostum;

class DashboardController extends Controller
{
    public function index()
{
    $title = 'Dashboard';

    $jumlah_data_admin = User::where('level', 'Admin')->count();
    $jumlah_data_pelanggan = User::where('level', 'User')->count();
    $jumlah_data_sewa = Sewa::count();

    $jumlah_sewa_avaible = Kostum::where('status_kostum', 'available')->count();
    $jumlah_sewa_rented = Kostum::where('status_kostum', 'Rented')->count();
    $jumlah_sewa_maintenance = Kostum::where('status_kostum', 'Maintenance')->count();

    return view('admin.dashboard.index', compact(
        'title',
        'jumlah_data_admin',
        'jumlah_data_pelanggan',
        'jumlah_data_sewa',
        'jumlah_sewa_avaible',
        'jumlah_sewa_rented',
        'jumlah_sewa_maintenance'
    ));
}
}
