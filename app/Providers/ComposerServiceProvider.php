<?php

namespace App\Providers;

use App\Http\ViewComposers\MenuPagesComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // при рендеринге layouts.app подключить MenuPagesComposer
        View::composer('layouts.app', MenuPagesComposer::class);
    }
}