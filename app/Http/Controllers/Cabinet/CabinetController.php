<?php

namespace App\Http\Controllers\Cabinet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CabinetController extends Controller
{
    public function index()
    {
        session()->put('active', 'dashboard');

        return view('cabinet.home');
    }
}
