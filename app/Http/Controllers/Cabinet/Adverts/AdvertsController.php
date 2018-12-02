<?php
/**
 * Created by PhpStorm.
 * User: dmitriy
 * Date: 02.12.18
 * Time: 21:36
 */

namespace App\Http\Controllers\Cabinet\Adverts;


use App\Http\Controllers\Controller;

class AdvertsController extends Controller
{
    public function index()
    {
        session()->put('active', 'adverts');

        return view('cabinet.adverts.index');
    }
}