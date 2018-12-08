<?php

namespace App\Http\Controllers;

use App\Http\Router\PagePath;

class PageController extends Controller
{
    /** Отображаем страницу по полученному адресу */
    public function show(PagePath $path)
    {
        $page = $path->page;
        return view('page', compact('page'));
    }
}