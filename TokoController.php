<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TokoController extends Controller
{
    public function index(){
        $title = "SHOP";
        return view("user.index",compact("title"));
    }
}
