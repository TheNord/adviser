<?php

namespace App\Http\ViewComposers;

use App\Entity\Page;
use Illuminate\View\View;

class MenuPagesComposer
{
    public function compose(View $view): void
    {
        // к переменной topMenuPages найти все страницы рутов c указанным menu_title с сортировкой по лефту и получить их модели
        $view->with('topMenuPages', Page::where('menu_title', '!=', 'null')->defaultOrder()->getModels());
    }
}