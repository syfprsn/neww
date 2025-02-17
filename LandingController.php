<?php

namespace App\Http\Controllers;

use App\Models\Kostum;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $kostum = Kostum::where('stok', '>', 0)->get();
        return view('landing.index', compact('kostum'));
    }
} 