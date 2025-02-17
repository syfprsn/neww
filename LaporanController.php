<?php
namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use PDF;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Laporan Pendapatan';
        
        $query = Transaksi::with(['sewa.user', 'sewa.bayar'])
                         ->whereHas('sewa', function($q) {
                             $q->where('status', 'approved');
                         });

        // Filter berdasarkan tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_transaksi', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        // Filter berdasarkan pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('sewa.user', function($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%");
                })
                ->orWhere('nama_kostum', 'like', "%{$search}%")
                ->orWhereHas('sewa.bayar', function($q) use ($search) {
                    $q->where('id_transaksi', 'like', "%{$search}%");
                });
            });
        }

        $transaksi = $query->orderBy('tgl_transaksi', 'desc')->get();
        $total_pendapatan = $transaksi->sum('total_harga');

        return view('admin.laporan.index', compact('title', 'transaksi', 'total_pendapatan'));
    }

    public function exportPDF(Request $request)
    {
        $query = Transaksi::with(['sewa.user', 'sewa.bayar'])
                         ->whereHas('sewa', function($q) {
                             $q->where('status', 'approved');
                         });

        // Filter berdasarkan tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_transaksi', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $transaksi = $query->orderBy('tgl_transaksi', 'desc')->get();
        $total_pendapatan = $transaksi->sum('total_harga');

        $periode = '';
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $periode = 'Periode: ' . Carbon::parse($request->start_date)->format('d/m/Y') . 
                      ' - ' . Carbon::parse($request->end_date)->format('d/m/Y');
        }

        $pdf = PDF::loadView('admin.laporan.pdf', compact('transaksi', 'total_pendapatan', 'periode'));
        return $pdf->download('laporan-pendapatan.pdf');
    }
}
